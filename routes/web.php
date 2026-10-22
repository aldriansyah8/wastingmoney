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

/**
 * Helper untuk mendeteksi user aktif dari sesi server Laravel maupun session 'wm_user'.
 * 
 * @return object|null Objek pengguna aktif atau null jika belum login
 */
if (!function_exists('getActiveWebUser')) {
    function getActiveWebUser() {
        return auth()->user() ?: (session('wm_user') ? (object)session('wm_user') : null);
    }
}

/**
 * Memeriksa apakah user memiliki hak akses sebagai Administrator (Admin Biasa atau Superadmin).
 * Aturan Bisnis:
 * - Role 'admin' atau 'superadmin'.
 * - Akun dengan domain/format email yang memuat 'admin'.
 * 
 * @param object|null $user Objek user yang diperiksa
 * @return bool True jika user tergolong administrator
 */
if (!function_exists('isAdminRole')) {
    function isAdminRole($user) {
        if (!$user) return false;
        $role = $user->role ?? '';
        $email = $user->email ?? '';
        return in_array($role, ['admin', 'superadmin']) || str_contains(strtolower($email), 'admin');
    }
}

// Halaman Utama Pengguna: Formulir Request Pencarian Merch / Barang Belanja
Route::get('/', function () {
    $user = getActiveWebUser();
    if (isAdminRole($user)) {
        return redirect('/admin');
    }
    return view('pages.home');
})->name('home');

// Halaman Otentikasi: Masuk (Sign In) & Daftar Akun (Sign Up)
Route::get('/auth', function () {
    $user = getActiveWebUser();
    if ($user) {
        return isAdminRole($user) ? redirect('/admin') : redirect('/');
    }
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
    $user = getActiveWebUser();
    if ($user && !isAdminRole($user)) {
        return redirect('/')->with('error', 'Akses ditolak: Hanya administrator yang dapat mengakses Dashboard Admin.');
    }
    return view('pages.admin');
})->name('admin');

// Halaman Merch Request List User: Grid Kartu & Modal Detail Dinamis Sesuai Tema
Route::get('/cart', function () {
    $user = getActiveWebUser();
    if (isAdminRole($user)) {
        return redirect('/admin');
    }
    return view('pages.user_list');
})->name('cart');

Route::get('/list', function () {
    $user = getActiveWebUser();
    if (isAdminRole($user)) {
        return redirect('/admin');
    }
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
    $user = getActiveWebUser();
    if (isAdminRole($user)) {
        return redirect('/admin');
    }
    return view('pages.profile');
})->name('profile');

Route::get('/account', function () {
    return redirect('/profile');
});

// Spesifikasi Dokumentasi REST API Server
Route::get('/api-spec', function () {
    return view('api-spec');
})->name('api-spec');

// Test Login Helpers for Admin and User
Route::get('/test-login-admin', function () {
    $admin = \App\Models\User::where('email', 'admin@rakapteman.id')->first() 
          ?: \App\Models\User::where('role', 'admin')->first();
    if ($admin) {
        auth()->login($admin);
        session(['wm_user' => $admin->toArray()]);
        return response("<!DOCTYPE html><html><body><script>
            localStorage.setItem('wm_user', JSON.stringify(" . json_encode($admin) . "));
            localStorage.setItem('wm_token', 'mock_token_admin');
            window.location.href = '/admin';
        </script></body></html>")->header('Content-Type', 'text/html');
    }
    return redirect('/admin');
});

Route::get('/test-login-user', function () {
    $user = \App\Models\User::where('email', 'user@rakapteman.id')->first()
         ?: \App\Models\User::where('role', 'user')->first();
    if ($user) {
        auth()->login($user);
        session(['wm_user' => $user->toArray()]);
        return response("<!DOCTYPE html><html><body><script>
            localStorage.setItem('wm_user', JSON.stringify(" . json_encode($user) . "));
            localStorage.setItem('wm_token', 'mock_token_user');
            window.location.href = '/cart';
        </script></body></html>")->header('Content-Type', 'text/html');
    }
    return redirect('/cart');
});

