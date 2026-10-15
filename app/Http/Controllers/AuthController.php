<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
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
     * Menangani pendaftaran akun pengguna baru (User / Admin).
     * 
     * Alur kerja:
     * - Validasi format email, keunikan email, dan minimal panjang password.
     * - Mendaftarkan user ke Firebase Authentication (jika kredensial aktif).
     * - Menyimpan data akun ke database lokal SQLite dengan password terenkripsi bcrypt.
     * - Menerbitkan Sanctum bearer token untuk autentikasi API.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $username = $request->input('username') ?: $request->input('name');

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:user,admin',
        ]);

        $name = $username ?: explode('@', $validated['email'])[0];
        $role = $validated['role'] ?? 'user';

        // Daftarkan ke Firebase Auth jika dikonfigurasi
        $firebaseUid = $this->firebase->createAuthUser(
            $validated['email'],
            $validated['password'],
            $name
        );

        // Simpan ke database lokal
        $user = User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'firebase_uid' => $firebaseUid,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi pengguna berhasil.',
            'token' => $token,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'firebase_uid' => $user->firebase_uid,
                ],
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'firebase_uid' => $user->firebase_uid,
            ],
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

        // Cari berdasarkan Email atau Nama/Username
        $user = User::where('email', strtolower($identifier))
            ->orWhere('name', $identifier)
            ->first();

        // Cek via Firebase Client Auth jika terhubung & user belum ada di DB lokal
        if (!$user && str_contains($identifier, '@')) {
            $fbAuthData = $this->firebase->verifyClientLogin($identifier, $password);
            if ($fbAuthData) {
                $user = User::create([
                    'name' => $fbAuthData['displayName'] ?? explode('@', $identifier)[0],
                    'email' => strtolower($identifier),
                    'password' => Hash::make($password),
                    'role' => 'user',
                    'firebase_uid' => $fbAuthData['localId'] ?? null,
                ]);
            }
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi Username/Email atau Password salah.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'firebase_uid' => $user->firebase_uid,
                ],
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'firebase_uid' => $user->firebase_uid,
            ],
        ]);
    }

    /**
     * Mengambil profil pengguna yang sedang aktif / login.
     * 
     * Mendukung multi-autentikasi:
     * - Melalui header Bearer Token Sanctum ($request->user())
     * - Melalui pencocokan email dari sesi / parameter
     * - Fallback graceful untuk demo mode
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user && $request->has('email')) {
            $user = User::where('email', strtolower($request->input('email')))->first();
        }

        if (!$user) {
            $user = User::first();
        }

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
        $user = $request->user();

        if (!$user && $request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        }

        if (!$user && $request->has('email')) {
            $user = User::where('email', strtolower($request->input('email')))->first();
        }

        if (!$user && $request->has('username')) {
            $user = User::where('name', $request->input('username'))->first();
        }

        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
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
}
