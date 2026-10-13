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
          <div class="px-4 py-2 border-b border-slate-100">
            <p class="text-xs text-slate-400 font-medium">Masuk sebagai:</p>
            <p id="dropdownName" class="text-sm font-bold text-slate-900 truncate">uccloud26</p>
            <p id="dropdownEmail" class="text-xs text-slate-500 truncate">uccloud26@gmail.com</p>
          </div>
          <button
            type="button"
            onclick="handleUserLogout()"
            class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors cursor-pointer"
          >
            <i data-lucide="log-out" class="w-4 h-4"></i>
            Logout
          </button>
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
              <div class="relative">
                <select
                  id="merchType"
                  name="merchType"
                  class="home-select-input"
                >
                  <option value="T-Shirt" selected>T-Shirt</option>
                  <option value="Vinyl">Vinyl</option>
                  <option value="CD">CD</option>
                  <option value="Hoodie">Hoodie</option>
                  <option value="Jacket">Jacket</option>
                  <option value="Poster">Poster</option>
                  <option value="Accessories">Accessories</option>
                  <option value="Other">Other</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-600 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
              </div>
            </div>

            <div>
              <label class="home-form-label">
                Released Year
              </label>
              <div class="relative">
                <select
                  id="releasedYear"
                  name="releasedYear"
                  class="home-select-input"
                >
                  <option value="2026" selected>2026</option>
                  <option value="2025">2025</option>
                  <option value="2024">2024</option>
                  <option value="2023">2023</option>
                  <option value="2022">2022</option>
                  <option value="2021">2021</option>
                  <option value="2020">2020</option>
                  <option value="2019">2019</option>
                  <option value="2018">2018</option>
                  <option value="2017">2017</option>
                  <option value="2016">2016</option>
                  <option value="2015">2015</option>
                  <option value="2010">2010</option>
                  <option value="2009">2009</option>
                  <option value="2000">2000</option>
                  <option value="1990s">1990s</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-600 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
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
  function toggleUserDropdown() {
    const d = document.getElementById('userDropdown');
    d.classList.toggle('hidden');
  }

  document.addEventListener('click', function(e) {
    const wrap = document.getElementById('userMenuWrapper');
    const d = document.getElementById('userDropdown');
    if (wrap && d && !wrap.contains(e.target)) {
      d.classList.add('hidden');
    }
  });

  function handleFileSelected(input) {
    if (input.files && input.files[0]) {
      document.getElementById('fileNameLabel').textContent = input.files[0].name;
    }
  }

  function handleUserLogout() {
    localStorage.removeItem('wm_user');
    localStorage.removeItem('rakap_token');
    window.location.href = '/auth';
  }

  // Sync user info if stored in localStorage
  (function syncUserInfo() {
    const stored = localStorage.getItem('wm_user');
    if (stored) {
      try {
        const u = JSON.parse(stored);
        if (u.name || u.username) document.getElementById('dropdownName').textContent = u.name || u.username;
        if (u.email) document.getElementById('dropdownEmail').textContent = u.email;
      } catch(e) {}
    }
  })();

  // Handle Merch Form Submit
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

    const token = localStorage.getItem('rakap_token');

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
