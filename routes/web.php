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

// Halaman Dashboard Admin: Review Pengajuan, Ubah Status, Tentukan Harga & Catatan
Route::get('/admin', function () {
    return view('pages.admin');
})->name('admin');

// Halaman Daftar Request User / Katalog: Grid 10 Kartu & Modal Detail Dinamis Sesuai Tema
Route::get('/list', function () {
    return view('pages.user_list');
})->name('user.list');

Route::get('/requests', function () {
    return redirect('/list');
});

Route::get('/catalog', function () {
    return redirect('/list');
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
