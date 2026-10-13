<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Wasting Money
| UI & UX persis sesuai desain client (Auth, Home Page User, Admin Review)
|--------------------------------------------------------------------------
*/

// Home Page User (Form Request)
Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Sign In & Sign Up Page
Route::get('/auth', function () {
    return view('pages.auth');
})->name('auth');

Route::get('/login', function () {
    return redirect('/auth');
})->name('login');

// Admin Review Dashboard
Route::get('/admin', function () {
    return view('pages.admin');
})->name('admin');

// REST API Specification (jika dibutuhkan untuk verifikasi endpoint)
Route::get('/api-spec', function () {
    return view('api-spec');
})->name('api-spec');
