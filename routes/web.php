<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Wasting Money
| UI & UX persis sesuai desain client (Auth, Home Page User, Admin Review)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Web Routes - Wasting Money
| UI & UX persis sesuai desain mockup client
|--------------------------------------------------------------------------
*/

// Halaman Utama Pengguna: Formulir Request Pencarian Merch / Barang Belanja
Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Halaman Otentikasi: Masuk (Sign In) & Daftar Akun (Sign Up)
Route::get('/auth', function () {
    return view('pages.auth');
})->name('auth');

Route::get('/login', function () {
    return redirect('/auth');
})->name('login');

// Endpoint Logout: Menghapus sesi server Laravel dan mengarahkan kembali ke halaman login (/auth)
Route::any('/logout', function (\Illuminate\Http\Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    $request->session()->forget(['wm_user', 'wm_role']);
    return redirect('/auth');
})->name('logout');

// Halaman Dashboard Admin: Review Pengajuan, Ubah Status, Tentukan Harga & Catatan
Route::get('/admin', function () {
    $user = auth()->user() ?: (session('wm_user') ? (object)session('wm_user') : null);
    if ($user && isset($user->role) && $user->role !== 'admin' && !str_contains($user->email ?? '', 'admin')) {
        return redirect('/list')->with('error', 'Akses ditolak: Hanya administrator yang dapat mengakses Dashboard Admin.');
    }
    return view('pages.admin');
})->name('admin');

// Halaman Keranjang Belanja / Request User (Keranjang): Grid Kartu & Modal Detail Dinamis Sesuai Tema
Route::get('/cart', function () {
    return view('pages.user_list');
})->name('cart');

Route::get('/list', function () {
    return view('pages.user_list');
})->name('user.list');

Route::get('/requests', function () {
    return redirect('/cart');
});

Route::get('/catalog', function () {
    return redirect('/cart');
});

// Halaman Edit Profil User & Modal Konfirmasi Simpan ("Are you sure want to save changes?")
Route::get('/profile', function () {
    return view('pages.profile');
})->name('profile');

Route::get('/account', function () {
    return redirect('/profile');
});

// Spesifikasi Dokumentasi REST API Server
Route::get('/api-spec', function () {
    return view('api-spec');
})->name('api-spec');
