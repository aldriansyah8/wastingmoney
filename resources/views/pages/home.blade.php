@extends('layouts.app')

@section('title', 'wastingmoney - Home')

@section('styles')
  <!-- Dedicated CSS for Home Page User (Separated from Blade) -->
  <link rel="stylesheet" href="/css/home.css">
@endsection

@section('content')
<div class="home-page-container">
  
  <!-- ================= TOP HEADER (Matching Home Page user.png) ================= -->
  <header class="home-header">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center select-none cursor-pointer">
      <img src="/assets/logo.png" alt="wastingmoney" class="home-logo-img" />
    </a>

    <!-- Right Icons: Cart & Profile -->
    <div class="flex items-center gap-4 text-slate-900 relative">
      <button 
        type="button" 
        onclick="showToast('Keranjang belanja kosong.')"
        class="p-1 hover:text-[#4e46e5] transition-colors cursor-pointer"
        title="Cart"
      >
        <i data-lucide="shopping-cart" class="w-5 sm:w-6 h-5 sm:h-6"></i>
      </button>

      <span class="text-slate-300 font-light select-none">|</span>

      <!-- User Icon with Dropdown -->
      <div class="relative" id="userMenuWrapper">
        <button 
          type="button" 
          onclick="toggleUserDropdown()"
          class="p-1 rounded-full hover:text-[#4e46e5] transition-colors cursor-pointer flex items-center"
          title="Profile"
        >
          <div class="w-6 sm:w-7 h-6 sm:h-7 rounded-full border border-slate-900 flex items-center justify-center">
            <i data-lucide="user" class="w-4 sm:w-4.5 h-4 sm:h-4.5 text-slate-900"></i>
          </div>
        </button>

        <div id="userDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50 animate-fadeIn">
          <!-- State: Authenticated -->
          <div id="authState" class="hidden">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Masuk sebagai:</p>
              <p id="dropdownName" class="text-sm font-bold text-slate-900 truncate">-</p>
              <p id="dropdownEmail" class="text-xs text-slate-500 truncate">-</p>
            </div>
            <a
              href="/profile"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="user-cog" class="w-4 h-4 text-[#4e46e5]"></i>
              Edit Profile
            </a>
            <a
              href="/list"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="layout-grid" class="w-4 h-4 text-[#4e46e5]"></i>
              My Requests (List Page)
            </a>
            <a
              href="/admin"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-indigo-600 hover:bg-indigo-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="shield-check" class="w-4 h-4"></i>
              Admin Dashboard
            </a>
            <button
              type="button"
              onclick="handleUserLogout()"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors cursor-pointer"
            >
              <i data-lucide="log-out" class="w-4 h-4"></i>
              Logout
            </button>
          </div>

          <!-- State: Unauthenticated (Guest / Belum Login) -->
          <div id="unauthState">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Status Akun:</p>
              <p class="text-sm font-semibold text-slate-700">Belum Masuk</p>
              <p class="text-xs text-slate-400">Silakan login untuk menyimpan histori request</p>
            </div>
            <a
              href="/list"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="layout-grid" class="w-4 h-4 text-[#4e46e5]"></i>
              Lihat Request (List Page)
            </a>
            <a
              href="/auth"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#4e46e5] hover:bg-indigo-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="log-in" class="w-4 h-4"></i>
              Masuk / Login
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="home-main-container">
    
    <!-- HERO CONCERT BANNER -->
    <div class="home-banner-wrapper">
      <img 
        src="/assets/hero_banner.png" 
        alt="Hunt down your goods and wasting your money" 
        class="home-banner-img"
      />
    </div>

    <!-- TWO-COLUMN SECTION: FORM (LEFT) & ARTWORK (RIGHT) -->
    <div class="home-grid-section">
      
      <!-- LEFT SIDE: REQUEST FORM -->
      <div class="home-grid-form-col">
        <div class="mb-6">
          <p class="home-subtitle">
            We heard you and we searching for you,
          </p>
          <h2 class="home-title">
            just request!
          </h2>
        </div>

        <!-- Alert Feedback Messages -->
        <div id="formSuccessAlert" class="hidden mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5">
          <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
          <span>Permintaan berhasil diajukan! Admin akan segera memprosesnya.</span>
        </div>

        <div id="formErrorAlert" class="hidden mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-start gap-2.5">
          <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
          <span id="formErrorMessage">Field Artist/band wajib diisi.</span>
        </div>

        <form id="merchForm" class="space-y-4">
          @csrf

          <!-- Artist/band -->
          <div>
            <label class="home-form-label">
              Artist/band
            </label>
            <input
              type="text"
              id="artistBand"
              name="artistBand"
              placeholder="Taylor Swift"
              required
              class="home-text-input"
            />
          </div>

          <!-- Merch Type & Released Year in one row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="home-form-label">
                Merch Type
              </label>
              <div class="wm-dropdown-wrapper" id="merchTypeWrapper">
                <button 
                  type="button" 
                  class="wm-dropdown-trigger home-dropdown-trigger" 
                  id="merchTypeBtn" 
                  onclick="toggleDropdownMenu('merchTypeMenu', 'merchTypeBtn')"
                >
                  <span id="merchTypeTriggerText">T-Shirt</span>
                  <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
                </button>
                <div class="wm-dropdown-menu home-dropdown-menu" id="merchTypeMenu">
                  <div class="wm-dropdown-item home-dropdown-item selected" data-value="T-Shirt" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'T-Shirt', 'T-Shirt', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>T-Shirt</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Vinyl" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Vinyl', 'Vinyl', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Vinyl</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="CD" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'CD', 'CD', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>CD</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Hoodie" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Hoodie', 'Hoodie', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Hoodie</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Jacket" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Jacket', 'Jacket', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Jacket</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Poster" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Poster', 'Poster', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Poster</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Accessories" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Accessories', 'Accessories', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Accessories</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  <div class="wm-dropdown-item home-dropdown-item" data-value="Other" onclick="selectGenericOption('merchType', 'merchTypeTriggerText', 'Other', 'Other', 'merchTypeMenu', 'merchTypeBtn')">
                    <span>Other</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                </div>
                <input type="hidden" id="merchType" name="merchType" value="T-Shirt" />
              </div>
            </div>

            <div>
              <label class="home-form-label">
                Released Year
              </label>
              <div class="wm-dropdown-wrapper" id="releasedYearWrapper">
                <button 
                  type="button" 
                  class="wm-dropdown-trigger home-dropdown-trigger" 
                  id="releasedYearBtn" 
                  onclick="toggleDropdownMenu('releasedYearMenu', 'releasedYearBtn')"
                >
                  <span id="releasedYearTriggerText">2026</span>
                  <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
                </button>
                <div class="wm-dropdown-menu home-dropdown-menu" id="releasedYearMenu">
                  @foreach(['2026', '2025', '2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016', '2015', '2010', '2009', '2000', '1990s'] as $yr)
                  <div 
                    class="wm-dropdown-item home-dropdown-item {{ $yr === '2026' ? 'selected' : '' }}" 
                    data-value="{{ $yr }}" 
                    onclick="selectGenericOption('releasedYear', 'releasedYearTriggerText', '{{ $yr }}', '{{ $yr }}', 'releasedYearMenu', 'releasedYearBtn')"
                  >
                    <span>{{ $yr }}</span>
                    <i data-lucide="check" class="wm-dropdown-check"></i>
                  </div>
                  @endforeach
                </div>
                <input type="hidden" id="releasedYear" name="releasedYear" value="2026" />
              </div>
            </div>
          </div>

          <!-- Merch Edition -->
          <div>
            <label class="home-form-label">
              Merch Edition
            </label>
            <input
              type="text"
              id="merchEdition"
              name="merchEdition"
              placeholder="Taylor Swift Nort America Tour"
              class="home-text-input"
            />
          </div>

          <!-- More Description -->
          <div>
            <label class="home-form-label">
              More Description
            </label>
            <textarea
              rows="4"
              id="moreDescription"
              name="description"
              placeholder="Taylor Swift North American Tour 2016, Long Sleeve, White with tour list"
              class="home-textarea-input"
            ></textarea>
          </div>

          <!-- Buttons Row: Upload Box + Submit Button -->
          <div class="flex items-start gap-3 pt-2">
            <!-- Upload Button -->
            <div class="flex flex-col items-center">
              <label class="home-upload-box">
                <i data-lucide="upload" class="w-5 h-5"></i>
                <input
                  type="file"
                  accept="image/*"
                  onchange="handleFileSelected(this)"
                  class="hidden"
                />
              </label>
              <span id="fileNameLabel" class="home-file-badge">
                img-01.jpg
              </span>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              id="submitButton"
              class="home-submit-btn"
            >
              <span id="submitLabel">Submit</span>
              <span id="submitLoading" class="hidden">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
              </span>
            </button>
          </div>
        </form>
      </div>

      <!-- RIGHT SIDE: POSTER ARTWORK -->
      <div class="home-grid-artwork-col home-artwork-wrapper">
        <img 
          src="/assets/poster_art.png" 
          alt="Life at your own rhythm" 
          class="home-artwork-img"
        />
      </div>

    </div>
  </main>

  <!-- ================= FOOTER ================= -->
  <footer class="home-footer">
    instagram &nbsp;|&nbsp; tiktok &nbsp;|&nbsp; threads &nbsp;|&nbsp; x
  </footer>
</div>
@endsection

@section('scripts')
<script>
  /**
   * Menampilkan atau menyembunyikan menu pop-up dropdown user di header.
   */
  function toggleUserDropdown() {
    const d = document.getElementById('userDropdown');
    d.classList.toggle('hidden');
  }

  // Menutup dropdown jika pengguna mengklik di luar area menu
  document.addEventListener('click', function(e) {
    const wrap = document.getElementById('userMenuWrapper');
    const d = document.getElementById('userDropdown');
    if (wrap && d && !wrap.contains(e.target)) {
      d.classList.add('hidden');
    }
  });

  /**
   * Event listener saat user memilih file gambar merch melalui dialog file.
   * Menampilkan nama file yang dipilih pada label badge.
   * 
   * @param {HTMLInputElement} input Elemen input file
   */
  function handleFileSelected(input) {
    if (input.files && input.files[0]) {
      document.getElementById('fileNameLabel').textContent = input.files[0].name;
    }
  }

  /**
   * Menghapus sesi otentikasi user (token & data user) dan memuat ulang halaman.
   */
  function handleUserLogout() {
    localStorage.removeItem('wm_user');
    localStorage.removeItem('wm_token');
    localStorage.removeItem('rakap_token');
    sessionStorage.clear();
    syncUserInfo();
    window.location.reload();
  }

  /**
   * Menyinkronkan status info user di header berdasarkan session token di localStorage.
   * Jika user sudah login, menampilkan nama dan email di menu profile dropdown.
   */
  function syncUserInfo() {
    const authState = document.getElementById('authState');
    const unauthState = document.getElementById('unauthState');
    const stored = localStorage.getItem('wm_user');
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    if (stored && token) {
      try {
        const u = JSON.parse(stored);
        if (u.name || u.username) document.getElementById('dropdownName').textContent = u.name || u.username;
        if (u.email) document.getElementById('dropdownEmail').textContent = u.email;
        if (authState && unauthState) {
          authState.classList.remove('hidden');
          unauthState.classList.add('hidden');
        }
        return;
      } catch(e) {}
    }
    if (authState && unauthState) {
      authState.classList.add('hidden');
      unauthState.classList.remove('hidden');
    }
  }
  syncUserInfo();

  /**
   * Event listener submit formulir pencarian/request merch baru.
   * Mengirim payload ke backend API (POST /api/submissions),
   * menyimpan ke database SQLite & Firebase Firestore, serta mereset form jika berhasil.
   */
  document.getElementById('merchForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const successEl = document.getElementById('formSuccessAlert');
    const errorEl = document.getElementById('formErrorAlert');
    const errorMsg = document.getElementById('formErrorMessage');
    const btn = document.getElementById('submitButton');
    const label = document.getElementById('submitLabel');
    const loading = document.getElementById('submitLoading');

    successEl.classList.add('hidden');
    errorEl.classList.add('hidden');

    const artistBand = document.getElementById('artistBand').value.trim();
    if (!artistBand) {
      errorMsg.textContent = 'Field Artist/band wajib diisi.';
      errorEl.classList.remove('hidden');
      return;
    }

    btn.disabled = true;
    label.textContent = 'Submitting...';
    loading.classList.remove('hidden');

    const payload = {
      artistBand: artistBand,
      merchType: document.getElementById('merchType').value,
      releasedYear: document.getElementById('releasedYear').value,
      merchEdition: document.getElementById('merchEdition').value.trim(),
      description: document.getElementById('moreDescription').value.trim(),
      additionalData: {
        fileName: document.getElementById('fileNameLabel').textContent.trim()
      }
    };

    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    try {
      const response = await fetch('/api/submissions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: JSON.stringify(payload)
      });

      const res = await response.json();

      if (res.success) {
        successEl.classList.remove('hidden');
        document.getElementById('artistBand').value = '';
        document.getElementById('merchEdition').value = '';
        document.getElementById('moreDescription').value = '';
        document.getElementById('fileNameLabel').textContent = 'img-01.jpg';
        selectGenericOption('merchType', 'merchTypeTriggerText', 'T-Shirt', 'T-Shirt', 'merchTypeMenu', 'merchTypeBtn');
        selectGenericOption('releasedYear', 'releasedYearTriggerText', '2026', '2026', 'releasedYearMenu', 'releasedYearBtn');
      } else {
        errorMsg.textContent = res.message || 'Gagal mengirim permintaan. Silakan coba lagi.';
        errorEl.classList.remove('hidden');
      }
    } catch(err) {
      errorMsg.textContent = 'Gagal mengirim permintaan. Periksa koneksi jaringan.';
      errorEl.classList.remove('hidden');
    } finally {
      btn.disabled = false;
      label.textContent = 'Submit';
      loading.classList.add('hidden');
      lucide.createIcons();
    }
  });
</script>
@endsection
