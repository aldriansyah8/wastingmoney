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

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Wasting Money Laravel API Server is running smoothly',
        'app' => 'Wasting Money',
        'framework' => 'Laravel ' . app()->version(),
        'timestamp' => now()->toIso8601String(),
    ]);
});

// 1. Autentikasi
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
});

// 2. Pengajuan Formulir (User)
Route::post('/submissions', [SubmissionController::class, 'store']);
Route::middleware('auth:sanctum')->get('/submissions/my', [SubmissionController::class, 'mySubmissions']);

// 3. Katalog Publik (Approved Items)
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{slug}', [ItemController::class, 'show']);

// 4. Manajemen Admin (Approval Flow)
Route::prefix('admin')->group(function () {
    Route::get('/submissions', [AdminSubmissionController::class, 'index']);
    Route::patch('/submissions/{id}/status', [AdminSubmissionController::class, 'updateStatus']);
    Route::patch('/submissions/{id}/approve', [AdminSubmissionController::class, 'approve']);
    Route::patch('/submissions/{id}/reject', [AdminSubmissionController::class, 'reject']);
});

// 5. Firebase Connection Test Endpoint
Route::get('/firebase/test-connection', function (\App\Services\FirebaseService $firebase) {
    return response()->json([
        'success' => true,
        'message' => 'Firebase Diagnostic Status',
        'diagnostics' => $firebase->testConnection(),
    ]);
});

