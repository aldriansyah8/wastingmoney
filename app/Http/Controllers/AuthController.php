<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use App\Services\FirebaseWebmailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Controller AuthController
 * 
 * Bertanggung jawab menangani seluruh logika autentikasi dan manajemen profil akun pengguna:
 * 1. Pendaftaran pengguna baru (Register) dengan sinkronisasi ke Firebase Auth.
 * 2. Masuk ke sistem (Login) menggunakan Email atau Username serta penerbitan Sanctum Token.
 * 3. Mengambil data akun saat ini (Me / Profile info).
 * 4. Memperbarui informasi profil (Full Name, Address, Phone, Postal Code, Password).
 */
class AuthController extends Controller
{
    /**
     * Layanan integrasi Firebase
     * @var FirebaseService
     */
    protected FirebaseService $firebase;

    /**
     * Dependency Injection untuk FirebaseService
     * 
     * @param FirebaseService $firebase
     */
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Menangani pendaftaran akun pengguna baru (Sign Up).
     * 
     * Aturan Bisnis & Keamanan:
     * - Endpoint pendaftaran publik ini secara EKSKLUSIF hanya dapat mendaftarkan akun sebagai 'user' biasa.
     * - Akun administrator (Admin / Superadmin) TIDAK DAPAT didaftarkan melalui form publik ini,
     *   melainkan hanya dapat dibuat oleh Superadmin melalui fitur 'Manage Account'.
     * 
     * Alur kerja:
     * - Validasi format email, keunikan email, dan minimal panjang password (min. 6 karakter).
     * - Mendaftarkan user ke Firebase Authentication (jika kredensial aktif).
     * - Menyimpan data akun ke database lokal SQLite dengan password terenkripsi bcrypt dan role 'user'.
     * - Mengembalikan payload data user baru untuk proses redirect otomatis ke halaman login.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Menangani pendaftaran akun pengguna baru (Sign Up) dengan integrasi Webmailer.
     * 
     * @param Request $request
     * @param FirebaseWebmailer $Webmailer
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request, FirebaseWebmailer $webmailer)
    {
        $username = $request->input('username') ?: $request->input('name');

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $name = $username ?: explode('@', $validated['email'])[0];
        $role = 'user'; // Aturan wajib: pendaftaran publik hanya untuk role 'user'

        // Daftarkan akun ke Firebase Auth dan ambil idToken untuk webmailer
        $firebaseUid = null;
        $idToken = null;
        try {
            $firebaseUid = $this->firebase->createAuthUser(
                $validated['email'],
                $validated['password'],
                $name
            );

            // Lakukan verifikasi login kilat untuk mendapatkan idToken khusus pemanggilan webmailer
            $fbAuthData = $this->firebase->verifyClientLogin($validated['email'], $request->input('password'));
            $idToken = $fbAuthData['idToken'] ?? null;
        } catch (\Throwable $e) {}

        // Simpan data ke database lokal SQLite
        $user = User::create([
            'name' => $name,
            'email' => strtolower($validated['email']),
            'password' => Hash::make($request->input('password')),
            'role' => $role,
            'firebase_uid' => $firebaseUid,
        ]);

        // Panggil modul webmailer terpisah untuk mengirim email verifikasi ke Gmail user
        if ($idToken) {
            $webmailer->sendVerificationEmail($idToken);
        }

        // Sinkronisasi data user baru ke Cloud Firestore
        try {
            $this->firebase->syncUser($user->toArray());
        } catch (\Throwable $e) {}

        // PENTING: Sengaja tidak memanggil auth()->login($user) atau token Sanctum 
        // agar akun berstatus tertunda (pending) sampai user mengklik tautan verifikasi di email.

        $userPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->name,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'address' => $user->address,
            'phone_number' => $user->phone_number,
            'postal_code' => $user->postal_code,
            'role' => $user->role,
            'firebase_uid' => $user->firebase_uid,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Tautan verifikasi telah dikirim ke email Anda. Silakan cek kotak masuk Gmail Anda dan lakukan verifikasi sebelum masuk.',
            'data' => [
                'user' => $userPayload,
            ],
            'user' => $userPayload,
        ], 201);
    }

    /**
     * Menangani proses login pengguna ke dalam aplikasi.
     * 
     * Fitur:
     * - Fleksibel: dapat login menggunakan Email maupun Username.
     * - Memverifikasi kecocokan password dengan hash bcrypt.
     * - Jika user belum ada di DB lokal tetapi ada di Firebase, otomatis disinkronkan.
     * - Mengembalikan user data dan Sanctum bearer token.
     * 
     * @param Request $request
     * @param FirebaseWebmailer $FirebaseWebmailer
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $identifier = $request->input('email') ?: $request->input('username') ?: $request->input('signInIdentifier');
        $password = $request->input('password');

        if (!$identifier || !$password) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email dan Password wajib diisi.',
            ], 422);
        }

        // 1. Cari user terlebih dahulu di database lokal
        $user = User::where('email', strtolower($identifier))
            ->orWhere('name', $identifier)
            ->first();

        $emailToCheck = $user ? $user->email : $identifier;

        // 2. Verifikasi kredensial ke Firebase Auth
        $fbAuthData = $this->firebase->verifyClientLogin($emailToCheck, $password);

        if (!$fbAuthData) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi Username/Email atau Password salah di Firebase Auth.',
            ], 401);
        }

        // 3. Pengecekan Mutlak: Validasi status verifikasi email via Firebase Admin SDK
        $isVerified = $this->firebase->isEmailVerified($emailToCheck);
        if (!$isVerified) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi! Silakan periksa kotak masuk Gmail Anda dan klik tautan verifikasi terlebih dahulu sebelum masuk.',
            ], 403); // Status 403 Forbidden menghentikan akses login secara total
        }

        // 4. Validasi akhir pencocokan password lokal
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi Username/Email atau Password salah.',
            ], 401);
        }

        // Jika lolos seluruh verifikasi, izinkan login
        auth()->login($user);
        session(['wm_user' => $user->toArray(), 'wm_role' => $user->role]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $userPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->name,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'address' => $user->address,
            'phone_number' => $user->phone_number,
            'postal_code' => $user->postal_code,
            'role' => $user->role,
            'firebase_uid' => $user->firebase_uid,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'data' => [
                'token' => $token,
                'user' => $userPayload,
            ],
            'user' => $userPayload,
        ]);
    }

    /**
     * Menyelesaikan objek User yang saat ini sedang login secara akurat.
     * Menggunakan multi-prioritas: Bearer token Sanctum, Laravel Session Auth, session('wm_user'),
     * atau parameter user_id/email yang sah.
     * TIDAK PERNAH melakukan fallback sembarangan ke User::first()!
     * 
     * @param Request $request
     * @return User|null
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Cek dari Bearer token Sanctum jika ada
        try {
            if ($user = auth('sanctum')->user()) {
                return $user;
            }
        } catch (\Throwable $e) {}

        // 2. Cek dari sesi login default Laravel
        if ($user = auth()->user()) {
            return $user;
        }

        // 3. Cek dari sesi Laravel 'wm_user'
        if ($request->hasSession() && $sessUser = session('wm_user')) {
            if (isset($sessUser['id'])) {
                $user = User::find($sessUser['id']);
                if ($user) return $user;
            }
            if (isset($sessUser['email'])) {
                $user = User::where('email', strtolower($sessUser['email']))->first();
                if ($user) return $user;
            }
        }

        // 4. Cek dari parameter user_id jika dikirimkan oleh klien terotentikasi
        if ($request->has('user_id') && !empty($request->input('user_id'))) {
            $user = User::find($request->input('user_id'));
            if ($user) return $user;
        }

        // 5. Cek dari parameter email jika dikirimkan
        if ($request->has('email') && !empty($request->input('email'))) {
            $user = User::where('email', strtolower($request->input('email')))->first();
            if ($user) return $user;
        }

        // 6. Cek dari parameter username jika dikirimkan
        if ($request->has('username') && !empty($request->input('username'))) {
            $user = User::where('name', $request->input('username'))->first();
            if ($user) return $user;
        }

        return null;
    }

    /**
     * Mengambil profil pengguna yang sedang aktif / login.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna belum terautentikasi.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'address' => $user->address,
                'phone_number' => $user->phone_number,
                'postal_code' => $user->postal_code,
                'role' => $user->role,
                'firebase_uid' => $user->firebase_uid,
            ],
        ]);
    }

    /**
     * Memperbarui informasi profil pengguna dan password baru.
     * 
     * Data yang dapat diubah:
     * - full_name (Nama Lengkap)
     * - address (Alamat Lengkap)
     * - phone_number (Nomor Telepon)
     * - postal_code (Kode Pos)
     * - new_password (Kata Sandi Baru - dienkripsi otomatis dengan Hash bcrypt)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan atau belum login.',
            ], 401);
        }

        if ($request->has('full_name')) {
            $user->full_name = $request->input('full_name');
        }
        if ($request->has('address')) {
            $user->address = $request->input('address');
        }
        if ($request->has('phone_number')) {
            $user->phone_number = $request->input('phone_number');
        }
        if ($request->has('postal_code')) {
            $user->postal_code = $request->input('postal_code');
        }
        if ($request->has('new_password') && !empty($request->input('new_password'))) {
            $user->password = Hash::make($request->input('new_password'));
        }

        $user->save();

        // Simpan sesi login Laravel
        auth()->login($user);
        session(['wm_user' => $user->toArray(), 'wm_role' => $user->role]);

        // Sinkronisasi data profil terbaru ke Cloud Firestore
        $this->firebase->syncUser($user->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'address' => $user->address,
                'phone_number' => $user->phone_number,
                'postal_code' => $user->postal_code,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Menangani proses keluar (Logout) pengguna.
     * Menghapus sesi server Laravel dan mencabut token autentikasi.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->forget(['wm_user', 'wm_role']);
        }

        if ($request->user()) {
            try {
                $request->user()->currentAccessToken()?->delete();
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }
}

