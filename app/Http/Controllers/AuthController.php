<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected FirebaseService $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

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

        // Cek via Firebase Client Auth jika terhubung & user belum ada
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

    public function me(Request $request)
    {
        $user = $request->user();

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
                'email' => $user->email,
                'role' => $user->role,
                'firebase_uid' => $user->firebase_uid,
            ],
        ]);
    }
}
