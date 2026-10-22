<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AdminSubmissionController;
use App\Http\Controllers\ItemController;

/*
|--------------------------------------------------------------------------
| API Routes - Wasting Money
|--------------------------------------------------------------------------
*/

// Endpoint Uji Kesehatan Server (Healthcheck)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Wasting Money Laravel API Server is running smoothly',
        'app' => 'Wasting Money',
        'framework' => 'Laravel ' . app()->version(),
        'timestamp' => now()->toIso8601String(),
    ]);
});

// 1. Grup Endpoint Autentikasi & Profil Pengguna
Route::prefix('auth')->group(function () {
    // Mendaftar akun baru (nama, email, password)
    Route::post('/register', [AuthController::class, 'register']);
    
    // Masuk sistem dengan email & password
    Route::post('/login', [AuthController::class, 'login']);
    
    // Mendapatkan data user saat ini yang sedang aktif
    Route::get('/me', [AuthController::class, 'me']);
    
    // Menyimpan pembaruan profil user (username, nama lengkap, alamat, no hp, kode pos, password)
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    
    // Keluar dari sistem (Logout) dan menghapus sesi
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);
    
    // Middleware Sanctum (opsional untuk Bearer token security)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/sanctum/me', [AuthController::class, 'me']);
        Route::post('/sanctum/profile', [AuthController::class, 'updateProfile']);
    });
});

// 2. Grup Endpoint Pengajuan Formulir Merch (User)
// Mengambil seluruh pengajuan merchandise untuk tampilan daftar/katalog user realtime
Route::get('/submissions', [SubmissionController::class, 'index']);
// Mengirimkan request barang baru (artist/band, jenis merch, tahun rilis, deskripsi, upload file)
Route::post('/submissions', [SubmissionController::class, 'store']);
// Mengambil daftar pengajuan belanja milik user (dari sanctum auth atau query session)
Route::match(['get', 'post'], '/submissions/my', [SubmissionController::class, 'mySubmissions']);
// Memperbarui status request oleh user (misal: Lanjut -> Paid / Cancel -> Dibatalkan)
Route::patch('/submissions/{id}/status', [SubmissionController::class, 'updateStatus']);

// 3. Grup Endpoint Katalog Publik (Approved Items)
// Mengambil daftar seluruh barang yang telah disetujui admin untuk ditampilkan di katalog
Route::get('/items', [ItemController::class, 'index']);
// Mengambil detail lengkap satu barang berdasarkan slug unik
Route::get('/items/{slug}', [ItemController::class, 'show']);

// 4. Grup Endpoint Manajemen Admin (Review & Approval Flow)
Route::prefix('admin')->group(function () {
    // Mengambil seluruh daftar pengajuan dari semua user untuk tabel dashboard admin
    Route::get('/submissions', [AdminSubmissionController::class, 'index']);
    
    // Memperbarui status pengajuan, nominal harga yang disetujui, mata uang, dan catatan seller/admin
    Route::patch('/submissions/{id}/status', [AdminSubmissionController::class, 'updateStatus']);
    
    // Shortcut aksi cepat untuk menyetujui (Approve) pengajuan
    Route::patch('/submissions/{id}/approve', [AdminSubmissionController::class, 'approve']);
    
    // Shortcut aksi cepat untuk menolak (Reject) pengajuan
    Route::patch('/submissions/{id}/reject', [AdminSubmissionController::class, 'reject']);

    // Menghapus data formulir pengajuan request dari user oleh Administrator
    Route::delete('/submissions/{id}', [AdminSubmissionController::class, 'deleteSubmission']);

    // Mengambil daftar seluruh admin (Admin Biasa & Superadmin)
    Route::get('/admins', [AdminSubmissionController::class, 'getAdmins']);

    // Menambahkan akun Admin baru oleh Superadmin (Form style registrasi user)
    Route::post('/admins', [AdminSubmissionController::class, 'createAdmin']);

    // Mengambil seluruh akun (User & Admin) untuk fitur Manage Account oleh Superadmin
    Route::get('/accounts', [AdminSubmissionController::class, 'getAllAccounts']);

    // Mengubah role akun (User <-> Admin <-> Superadmin) oleh Superadmin
    Route::patch('/accounts/{id}/role', [AdminSubmissionController::class, 'updateAccountRole']);

    // Menghapus akun oleh Superadmin
    Route::delete('/accounts/{id}', [AdminSubmissionController::class, 'deleteAccount']);
});

// 5. Endpoint Diagnostik Koneksi Google Firebase & Cloud Firestore
Route::get('/firebase/test-connection', function (\App\Services\FirebaseService $firebase) {
    return response()->json([
        'success' => true,
        'message' => 'Firebase Diagnostic Status',
        'diagnostics' => $firebase->testConnection(),
    ]);
});

