# Wasting Money - Laravel Fullstack & REST API Application

Aplikasi sistem input formulir merchandise musik dengan alur verifikasi admin (**Admin Review & Approval Flow**), sinkronisasi otomatis ke **Firebase (Cloud Firestore & Firebase Auth)** menggunakan Service Account Google Cloud, serta basis data **SQLite** berbasis framework **Laravel**.

Desain antarmuka (UI/UX) dan tata letak dibangun 100% presisi mengikuti dokumen desain client tanpa inline `<style>` di Blade.

---

## 🚀 Fitur & Modul Utama

1. **User Home & Submission Flow (`/`)**:
   - Mengikuti desain `Home Page user.png` dengan banner artistik band, formulir 2 kolom, input field custom, upload file merch, tombol *Upload* abu gelap (`#2b2b2b`), dan tombol *Submit* indigo (`#4e46e5`).
   - Setiap pengajuan mendapatkan Request ID berurutan 7 digit (contoh: `#0000001`).

2. **Autentikasi Pengguna & Admin (`/auth`)**:
   - Mengikuti desain `Sign in.png` & `Sign up.png` dengan maskot 3D karakter, input ungu lembut (`#f0f1fe`), tombol switch tab Login/Register, dan toggle visibilitas password.
   - Terintegrasi dengan token Sanctum dan sinkronisasi ke Firebase Auth.

3. **Admin Dashboard & Review Modal (`/admin`)**:
   - Mengikuti desain `admin_page1.png` & `admin_page2.png` (serta mockup 2-kolom client).
   - Menampilkan tabel seluruh pengajuan dengan tombol info `(i)` per baris.
   - **Review Modal 2-Kolom Presisi**:
     - **Kolom Kiri**: Galeri foto produk merch (preview foto utama + deretan 3 thumbnail yang dapat diklik bergantian + box indikator *"Max 3 Photo"*).
     - **Kolom Kanan**: Data lengkap pengajuan (`Request ID`, indikator hijau `● Found`, `Username`, `Full Name`, `Address`, `Postal Code`, `Phone Number`, `Artist/Band`, `Merch Type`, `Released Year`, `Merch Edition`, `More Description`), dropdown status (`In Progress`, `Approved`, `Rejected`), input `Price`, input `Seller Notes`.
     - **Tombol Aksi**: Tombol **Save** berwarna biru/ungu (`#4c4ee7`) dan tombol **Cancel** berwarna merah koral (`#d9434e`).

4. **Integrasi Firebase Service Account**:
   - Menggunakan kredensial resmi [`serviceAccountKey.json`](serviceAccountKey.json) untuk project `wastingmoney-1cd45`.
   - Menghasilkan token OAuth2 Google IAM Bearer secara otomatis tanpa library berat gRPC.
   - Sinkronisasi pengajuan formulir ke Cloud Firestore REST API secara realtime.
   - Endpoint diagnostik live koneksi Firebase di `/api/firebase/test-connection`.

5. **Arsitektur Pemisahan CSS (Separation of Concerns)**:
   - **0% inline `<style>`** di dalam file Blade/PHP.
   - CSS Admin disatukan dalam 1 file: [`public/css/admin.css`](public/css/admin.css).
   - CSS User dipisah per halaman:
     - [`public/css/home.css`](public/css/home.css) untuk halaman pengajuan user.
     - [`public/css/auth.css`](public/css/auth.css) untuk halaman login & register.
     - [`public/css/base.css`](public/css/base.css) untuk typography (Plus Jakarta Sans) & reset root.
     - [`public/css/api.css`](public/css/api.css) untuk dokumentasi & endpoint viewer.

---

## 🛠️ Panduan Menjalankan Aplikasi

### 1. Prasyarat
- PHP 8.2 atau lebih baru
- Composer
- SQLite (ekstensi pdo_sqlite aktif)

### 2. Instalasi Dependensi & Setup Environment
```bash
# Salin konfigurasi environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Migrasi & isi data awal (seeder)
php artisan migrate:fresh --seed
```

### 3. Menjalankan Server Lokal
```bash
php artisan serve --port=8000
```
Aplikasi dapat langsung diakses di browser:
- **Halaman User (Home Form)**: [http://localhost:8000/](http://localhost:8000/)
- **Halaman Login / Register**: [http://localhost:8000/auth](http://localhost:8000/auth)
- **Halaman Admin Review Table**: [http://localhost:8000/admin](http://localhost:8000/admin)
- **Firebase Live Test Status**: [http://localhost:8000/api/firebase/test-connection](http://localhost:8000/api/firebase/test-connection)

---

## 🔑 Kredensial Akun Pengujian (dari `accounts.txt`)

| Role | Nama Pengguna | Email | Password |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin` | `admin@wastingmoney.id` | `R4k4p#Adm!n2026$Sec` |
| **User (Komunitas)** | `uccloud26` | `uccloud26@gmail.com` | `Dim4s#User2026!Pass` |
| **User (Mitra)** | `siti` | `siti.rahmayanti@mitra.org` | `S1t1#Mitra2026%Key` |

---

## 📡 Daftar Endpoint REST API (`/api`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/health` | Publik | Health check server Laravel |
| `GET` | `/api/firebase/test-connection` | Publik | Diagnostik koneksi OAuth2 & status Cloud Firestore / Auth |
| `POST` | `/api/auth/register` | Publik | Registrasi pengguna baru & sinkronisasi ke Firebase |
| `POST` | `/api/auth/login` | Publik | Autentikasi pengguna / admin dan penerbitan token |
| `GET` | `/api/auth/me` | Sanctum | Profil pengguna yang sedang login |
| `POST` | `/api/submissions` | Publik/User | Pengajuan formulir merch baru (generate Request ID + kirim ke Firestore) |
| `GET` | `/api/submissions/my` | Sanctum | Riwayat pengajuan milik pengguna yang sedang login |
| `GET` | `/api/items` | Publik | Daftar katalog publik barang yang disetujui (`Approved`) |
| `GET` | `/api/items/{slug}` | Publik | Detail item publik via clean URL slug |
| `GET` | `/api/admin/submissions` | Admin | Menampilkan daftar seluruh pengajuan formulir masuk |
| `PATCH`| `/api/admin/submissions/{id}/status` | Admin | Simpan perubahan status, harga (`price`), dan catatan (`seller_notes`) |
| `PATCH`| `/api/admin/submissions/{id}/approve` | Admin | Menyetujui pengajuan agar terbit ke katalog |
| `PATCH`| `/api/admin/submissions/{id}/reject` | Admin | Menolak pengajuan formulir |

Daftar request lengkap dapat diuji menggunakan file HTTP Client di [`api_requests.http`](api_requests.http).

---

## 📁 Struktur Berkas Proyek

```
wasting_money/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php            # Logika Register, Login, Profil
│   │   ├── SubmissionController.php      # Logika submit formulir user
│   │   ├── AdminSubmissionController.php # Logika review tabel & modal approval admin
│   │   └── ItemController.php             # Logika katalog item publik
│   ├── Models/
│   │   ├── User.php
│   │   ├── Submission.php
│   │   ├── Item.php
│   │   └── Notification.php
│   └── Services/
│       └── FirebaseService.php           # Service Account OAuth2 & Cloud Firestore REST
├── public/
│   ├── css/
│   │   ├── base.css                      # Global font Plus Jakarta Sans & body reset
│   │   ├── home.css                      # Dedicated styling halaman Home User
│   │   ├── auth.css                      # Dedicated styling halaman Login/Register
│   │   ├── admin.css                     # Dedicated styling halaman Admin & Modal Review
│   │   └── api.css                       # Styling dokumentasi endpoint
│   └── assets/                           # Logo, banner, artwork karakter
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php             # Master layout bersih tanpa inline style
│       └── pages/
│           ├── home.blade.php            # Halaman formulir request (User)
│           ├── auth.blade.php            # Halaman login/register (User/Admin)
│           └── admin.blade.php           # Halaman tabel review pengajuan (Admin)
├── routes/
│   ├── api.php                           # Seluruh rute REST API
│   └── web.php                           # Rute rendering view Blade
├── serviceAccountKey.json                # Kredensial Firebase Service Account (gitignored)
├── .gitignore                            # Satu file gitignore di root proyek
└── README.md
```
