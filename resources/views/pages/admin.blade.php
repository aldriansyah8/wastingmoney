@extends('layouts.app')

@section('title', 'wastingmoney - Admin')

@section('styles')
  <!-- Dedicated CSS for Admin Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/admin.css">
@endsection

@section('content')
<div class="admin-page-container selection:bg-indigo-100 selection:text-indigo-800">
  
  <!-- ================= TOP HEADER (Matching admin_page1.png) ================= -->
  <header class="admin-header">
    <!-- Brand Logo -->
    <a href="/admin" class="flex items-center select-none cursor-pointer">
      <img src="/assets/logo.png" alt="wastingmoney" class="admin-logo-img" />
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
      <div class="relative" id="adminMenuWrapper">
        <button 
          type="button" 
          onclick="toggleAdminDropdown()"
          class="p-1 rounded-full hover:text-[#4e46e5] transition-colors cursor-pointer flex items-center"
          title="Admin Profile"
        >
          <div class="w-6 sm:w-7 h-6 sm:h-7 rounded-full border border-slate-900 flex items-center justify-center">
            <i data-lucide="user" class="w-4 sm:w-4.5 h-4 sm:h-4.5 text-slate-900"></i>
          </div>
        </button>

        <div id="adminDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50">
          <div id="adminAuthState" class="hidden">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Administrator:</p>
              <p id="adminDropdownName" class="text-sm font-bold text-slate-900 truncate">System Administrator</p>
              <p id="adminDropdownEmail" class="text-xs text-slate-500 truncate">admin@wastingmoney.id</p>
            </div>
            <a
              href="/list"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="layout-grid" class="w-4 h-4 text-[#4e46e5]"></i>
              User Request List
            </a>
            <a
              href="/"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="home" class="w-4 h-4 text-slate-500"></i>
              Home Page User
            </a>
            <button
              type="button"
              onclick="handleAdminLogout()"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors cursor-pointer"
            >
              <i data-lucide="log-out" class="w-4 h-4"></i>
              Logout
            </button>
          </div>
          <div id="adminUnauthState">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Status Admin:</p>
              <p class="text-sm font-semibold text-rose-600">Belum Login Admin</p>
              <p class="text-xs text-slate-400">Silakan login sebagai admin</p>
            </div>
            <a
              href="/list"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="layout-grid" class="w-4 h-4 text-[#4e46e5]"></i>
              User Request List
            </a>
            <a
              href="/auth"
              class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#4e46e5] hover:bg-indigo-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="log-in" class="w-4 h-4"></i>
              Login Admin
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ================= MAIN CONTENT: REQUESTS TABLE (Matching admin_page1.png) ================= -->
  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 py-6 sm:py-10">
    
    <!-- Table Container -->
    <div class="admin-table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="py-4 px-3">
              <div class="th-content">
                No <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Request ID <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Username <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Artist/Band <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Merch Type <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Released Year <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Status <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Request Date <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-3 text-center"></th>
          </tr>
        </thead>

        <tbody id="adminTableBody">
          <tr>
            <td colspan="9" class="py-12 text-center text-slate-400">
              <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto text-[#4e46e5] mb-2"></i>
              Memuat data pengajuan...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>

  <!-- ================= DETAIL & APPROVAL REVIEW MODAL (Matching Client Mockup Image 2) ================= -->
  <div id="adminReviewModal" class="hidden admin-modal-backdrop">
    <div class="admin-modal-card">
      <button
        type="button"
        onclick="closeAdminModal()"
        class="admin-modal-close-btn"
        title="Tutup Modal"
      >
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>

      <div class="admin-modal-layout">
        <!-- LEFT COLUMN: Product Gallery (Matching Mockup Image 2) -->
        <div class="admin-modal-gallery">
          <div class="admin-modal-main-img-wrap">
            <img id="modalPreviewImg" src="/assets/poster_art.png" alt="Merchandise Preview" class="admin-modal-main-img" />
          </div>

          <!-- Thumbnails Row -->
          <div class="admin-modal-thumbs-row">
            <div class="admin-thumb-box" onclick="selectThumbImage(this)">
              <img id="thumb1" src="/assets/poster_art.png" alt="Thumb 1" />
            </div>
            <div class="admin-thumb-box" onclick="selectThumbImage(this)">
              <img id="thumb2" src="/assets/poster_art.png" alt="Thumb 2" />
            </div>
            <div class="admin-thumb-box" onclick="selectThumbImage(this)">
              <img id="thumb3" src="/assets/poster_art.png" alt="Thumb 3" />
            </div>
            <div class="admin-thumb-upload-box" title="Maksimal 3 Foto">
              <i data-lucide="upload" class="w-3.5 h-3.5 text-white"></i>
              <span>Max 3 Photo</span>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Details & Action Form (Matching Mockup Image 2) -->
        <div class="admin-modal-info">
          <!-- Top Row: Request ID & Found Date -->
          <div class="admin-info-header-row">
            <div>
              <div class="admin-info-request-label">Request ID</div>
              <div id="modalHeading" class="admin-info-request-id">#000001</div>
            </div>
            <div class="admin-info-found-badge">
              <span class="admin-found-dot"></span>
              <span id="modalFoundAt">Found 10/11/2026 10:08:55</span>
            </div>
          </div>

          <!-- Row: Username & Full Name -->
          <div class="admin-field-row-2">
            <div class="admin-field-item">
              <span class="admin-field-label">Username</span>
              <span id="infoUser" class="admin-field-val">uccloud26</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Full Name</span>
              <span id="infoFullName" class="admin-field-val">bukan manusia biasa</span>
            </div>
          </div>

          <!-- Row: Address & Postal Code -->
          <div class="admin-field-row-2">
            <div class="admin-field-item">
              <span class="admin-field-label">Address</span>
              <span id="infoAddress" class="admin-field-val truncate">Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Postal Code</span>
              <span id="infoPostalCode" class="admin-field-val">1945</span>
            </div>
          </div>

          <!-- Row: Phone Number -->
          <div class="admin-field-item">
            <span class="admin-field-label">Phone Number</span>
            <span id="infoPhone" class="admin-field-val">0813 888 8888</span>
          </div>

          <!-- Row: Artist/Band, Merch Type, Released Year -->
          <div class="admin-field-row-3">
            <div class="admin-field-item">
              <span class="admin-field-label">Artist/Band</span>
              <span id="infoArtistBand" class="admin-field-val">Taylor Swift</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Merch Type</span>
              <span id="infoMerchType" class="admin-field-val">T-Shirt</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Released Year</span>
              <span id="infoReleasedYear" class="admin-field-val">2016</span>
            </div>
          </div>

          <!-- Row: Merch Edition -->
          <div class="admin-field-item">
            <span class="admin-field-label">Merch Edition</span>
            <span id="infoEdition" class="admin-field-val">Taylor Swift North American Tour</span>
          </div>

          <!-- Row: More Description -->
          <div class="admin-field-item">
            <span class="admin-field-label">More Description</span>
            <span id="infoDesc" class="admin-field-val">Taylor Swift North American Tour 2016, Long Sleeve, White with tour list</span>
          </div>

          <!-- Input: Status Custom Dropdown -->
          <div class="admin-form-group">
            <label class="admin-form-label">Status</label>
            <div class="wm-dropdown-wrapper" id="statusDropdownWrapper">
              <button 
                type="button" 
                class="wm-dropdown-trigger" 
                id="statusDropdownBtn" 
                onclick="toggleDropdownMenu('statusDropdownMenu', 'statusDropdownBtn')"
              >
                <div class="wm-dropdown-trigger-content">
                  <span id="statusTriggerDot" class="wm-status-dot wm-status-dot-in-progress"></span>
                  <span id="statusTriggerText">In Progress</span>
                </div>
                <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
              </button>

              <div class="wm-dropdown-menu" id="statusDropdownMenu">
                <div class="wm-dropdown-item selected" data-value="In Progress" onclick="selectStatusOption('In Progress')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-in-progress"></span>
                    <span>In Progress</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item" data-value="Approved" onclick="selectStatusOption('Approved')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-approved"></span>
                    <span>Approved</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item" data-value="Rejected" onclick="selectStatusOption('Rejected')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-rejected"></span>
                    <span>Rejected</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item" data-value="Pending" onclick="selectStatusOption('Pending')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-pending"></span>
                    <span>Pending</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
              </div>
              <input type="hidden" id="modalStatus" value="In Progress" />
            </div>
          </div>

          <!-- Input: Price (Mata Uang & Nominal) -->
          <div class="admin-form-group">
            <label class="admin-form-label">Price</label>
            <div class="admin-price-group">
              <!-- Pilihan Mata Uang Custom Dropdown -->
              <div class="admin-price-currency-col">
                <div class="wm-dropdown-wrapper" id="currencyDropdownWrapper">
                  <button 
                    type="button" 
                    class="wm-dropdown-trigger admin-currency-trigger" 
                    id="currencyDropdownBtn" 
                    onclick="toggleDropdownMenu('currencyDropdownMenu', 'currencyDropdownBtn')"
                    title="Pilih Mata Uang"
                  >
                    <div class="wm-dropdown-trigger-content">
                      <span id="currencyTriggerText">IDR</span>
                    </div>
                    <i data-lucide="chevron-down" class="wm-dropdown-chevron"></i>
                  </button>

                  <div class="wm-dropdown-menu admin-currency-dropdown-menu" id="currencyDropdownMenu">
                    <div class="wm-dropdown-item selected" data-value="IDR" onclick="selectCurrencyOption('IDR', 'Rp')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">IDR</span>
                        <span>Rupiah (Rp)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="USD" onclick="selectCurrencyOption('USD', '$')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">USD</span>
                        <span>Dollar ($)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="EUR" onclick="selectCurrencyOption('EUR', '€')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">EUR</span>
                        <span>Euro (€)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="GBP" onclick="selectCurrencyOption('GBP', '£')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">GBP</span>
                        <span>Pound (£)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="JPY" onclick="selectCurrencyOption('JPY', '¥')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">JPY</span>
                        <span>Yen (¥)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="SGD" onclick="selectCurrencyOption('SGD', 'S$')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">SGD</span>
                        <span>Singapore (S$)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="AUD" onclick="selectCurrencyOption('AUD', 'A$')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">AUD</span>
                        <span>Australia (A$)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                    <div class="wm-dropdown-item" data-value="MYR" onclick="selectCurrencyOption('MYR', 'RM')">
                      <div class="wm-dropdown-item-left">
                        <span class="wm-currency-code-badge">MYR</span>
                        <span>Ringgit (RM)</span>
                      </div>
                      <i data-lucide="check" class="wm-dropdown-check"></i>
                    </div>
                  </div>
                  <input type="hidden" id="modalCurrency" value="IDR" />
                </div>
              </div>
              <!-- Input Nominal -->
              <div class="admin-price-amount-col">
                <span id="currencySymbolPrefix" class="admin-price-symbol-prefix">Rp</span>
                <input 
                  type="text" 
                  id="modalPriceAmount" 
                  class="admin-price-amount-input" 
                  placeholder="Masukkan nominal..."
                  autocomplete="off"
                  oninput="formatPriceNominal(this)"
                />
              </div>
            </div>
            <!-- Hidden input to maintain composite price string compatibility -->
            <input type="hidden" id="modalPrice" value="-" />
          </div>

          <!-- Input: Seller Notes -->
          <div class="admin-form-group">
            <label for="modalNotes" class="admin-form-label">Seller Notes</label>
            <input type="text" id="modalNotes" class="admin-form-input" placeholder="-" />
          </div>

          <!-- Action Buttons: Save (Blue) & Cancel (Red) -->
          <div class="admin-modal-actions">
            <button
              type="button"
              id="btnSaveModal"
              onclick="submitStatusUpdate()"
              class="admin-btn-save-modal"
            >
              <span id="labelSaveModal">Save</span>
              <span id="spinSaveModal" class="hidden">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
              </span>
            </button>

            <button
              type="button"
              onclick="closeAdminModal()"
              class="admin-btn-cancel-modal"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- ================= FOOTER (Matching admin_page1.png) ================= -->
  <footer class="admin-footer">
    instagram &nbsp;|&nbsp; tiktok &nbsp;|&nbsp; threads &nbsp;|&nbsp; x
  </footer>
</div>
@endsection

@section('scripts')
<script>
  let adminSubmissionsList = [];
  let selectedId = null;

  const currencySymbols = {
    'IDR': 'Rp',
    'USD': '$',
    'EUR': '€',
    'GBP': '£',
    'JPY': '¥',
    'SGD': 'S$',
    'AUD': 'A$',
    'MYR': 'RM'
  };

  const statusDotClasses = {
    'In Progress': 'wm-status-dot-in-progress',
    'Approved': 'wm-status-dot-approved',
    'Rejected': 'wm-status-dot-rejected',
    'Pending': 'wm-status-dot-pending'
  };

  /**
   * Mengubah pilihan status request pada custom dropdown modal review admin.
   * Mengupdate nilai input hidden, teks trigger, dan indikator warna status dot.
   * 
   * @param {string} status Nama status ('In Progress', 'Approved', 'Rejected', 'Pending')
   */
  function selectStatusOption(status) {
    const input = document.getElementById('modalStatus');
    const textEl = document.getElementById('statusTriggerText');
    const dotEl = document.getElementById('statusTriggerDot');
    
    if (input) input.value = status;
    if (textEl) textEl.textContent = status;
    if (dotEl) {
      dotEl.className = 'wm-status-dot ' + (statusDotClasses[status] || 'wm-status-dot-in-progress');
    }

    const menu = document.getElementById('statusDropdownMenu');
    if (menu) {
      menu.querySelectorAll('.wm-dropdown-item').forEach(item => {
        if (item.getAttribute('data-value') === status) {
          item.classList.add('selected');
        } else {
          item.classList.remove('selected');
        }
      });
      menu.classList.remove('show');
    }

    const btn = document.getElementById('statusDropdownBtn');
    if (btn) btn.classList.remove('open');
  }

  /**
   * Mengubah pilihan mata uang pada dropdown harga admin (IDR, USD, EUR, GBP, JPY, dll.).
   * Memperbarui simbol prefix di sebelah kiri input nominal (Rp, $, €, £, ¥, dll.).
   * 
   * @param {string} curr Kode mata uang ISO 3 huruf (contoh: 'IDR', 'USD')
   * @param {string} symbol Simbol mata uang (contoh: 'Rp', '$')
   * @param {string} label Label deskriptif opsional
   */
  function selectCurrencyOption(curr, symbol, label) {
    const input = document.getElementById('modalCurrency');
    const textEl = document.getElementById('currencyTriggerText');
    const prefixEl = document.getElementById('currencySymbolPrefix');

    if (input) input.value = curr;
    if (textEl) textEl.textContent = curr;
    if (prefixEl) prefixEl.textContent = symbol;

    const menu = document.getElementById('currencyDropdownMenu');
    if (menu) {
      menu.querySelectorAll('.wm-dropdown-item').forEach(item => {
        if (item.getAttribute('data-value') === curr) {
          item.classList.add('selected');
        } else {
          item.classList.remove('selected');
        }
      });
      menu.classList.remove('show');
    }

    const btn = document.getElementById('currencyDropdownBtn');
    if (btn) btn.classList.remove('open');

    updateModalPriceValue();
  }

  /**
   * Memformat input angka nominal harga dengan pemisah ribuan titik (contoh: 800000 -> 800.000).
   * 
   * @param {HTMLInputElement} input Elemen input teks nominal
   */
  function formatPriceNominal(input) {
    let raw = input.value.replace(/[^0-9]/g, '');
    if (raw) {
      input.value = new Intl.NumberFormat('id-ID').format(raw);
    } else {
      input.value = '';
    }
    updateModalPriceValue();
  }

  /**
   * Menggabungkan kode mata uang dan nominal harga menjadi string harga terpadu (contoh: "Rp 800.000").
   */
  function updateModalPriceValue() {
    const curr = document.getElementById('modalCurrency').value;
    const amount = document.getElementById('modalPriceAmount').value.trim();
    const symbol = currencySymbols[curr] || curr;
    const hiddenPrice = document.getElementById('modalPrice');
    if (!amount || amount === '0' || amount === '-') {
      hiddenPrice.value = '-';
    } else {
      hiddenPrice.value = `${symbol} ${amount}`;
    }
  }

  /**
   * Menampilkan atau menyembunyikan menu pop-up dropdown akun admin di header.
   */
  function toggleAdminDropdown() {
    const d = document.getElementById('adminDropdown');
    d.classList.toggle('hidden');
  }

  // Menutup dropdown akun admin saat pengguna mengklik di luar area menu
  document.addEventListener('click', function(e) {
    const wrap = document.getElementById('adminMenuWrapper');
    const d = document.getElementById('adminDropdown');
    if (wrap && d && !wrap.contains(e.target)) {
      d.classList.add('hidden');
    }
  });

  /**
   * Menghapus sesi otentikasi admin dan mengarahkan kembali ke halaman login.
   */
  function handleAdminLogout() {
    localStorage.removeItem('wm_user');
    localStorage.removeItem('wm_token');
    localStorage.removeItem('rakap_token');
    sessionStorage.clear();
    window.location.href = '/auth';
  }

  /**
   * Mengubah string format timestamp tanggal menjadi format DD/MM/YYYY.
   * 
   * @param {string} dateStr String tanggal ISO / datetime
   * @return {string} Tanggal terformat (DD/MM/YYYY)
   */
  function formatDate(dateStr) {
    if (!dateStr) return '10/10/2026';
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  }

  /**
   * Mengambil daftar data seluruh formulir pengajuan request dari backend API (/api/admin/submissions).
   */
  async function loadAdminSubmissions() {
    const tbody = document.getElementById('adminTableBody');
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    try {
      const response = await fetch('/api/admin/submissions', {
        headers: {
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        }
      });
      const res = await response.json();

      if (res.success && res.data) {
        adminSubmissionsList = res.data;
        renderAdminTable(res.data);
      } else {
        tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">Gagal memuat data formulir.</td></tr>`;
      }
    } catch(err) {
      tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">Koneksi terputus ke server.</td></tr>`;
    }
  }

  /**
   * Me-render daftar baris pengajuan formulir ke dalam tabel dashboard admin.
   * 
   * @param {Array} data Array berisi objek submission pengajuan belanja
   */
  function renderAdminTable(data) {
    const tbody = document.getElementById('adminTableBody');
    if (!data || data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="py-12 text-center text-slate-400">
            Belum ada data pengajuan formulir.
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = data.map((sub, idx) => `
      <tr class="hover:bg-slate-50/70 transition-colors text-xs sm:text-sm text-slate-800">
        <td class="py-4 px-3 font-normal">${idx + 1}</td>
        <td class="py-4 px-4 font-normal">${sub.requestId || String(idx + 1).padStart(7, '0')}</td>
        <td class="py-4 px-4 font-normal">${sub.submittedBy?.username || sub.submittedBy?.name || 'uccloud26'}</td>
        <td class="py-4 px-4 font-normal">${sub.artistBand || sub.title || 'Taking Back Sunday'}</td>
        <td class="py-4 px-4 font-normal">${sub.merchType || sub.category || 'Vinyl'}</td>
        <td class="py-4 px-4 font-normal">${sub.releasedYear || '2009'}</td>
        <td class="py-4 px-4 font-normal">${sub.status || 'In Progress'}</td>
        <td class="py-4 px-4 font-normal">${formatDate(sub.createdAt || sub.created_at)}</td>
        <td class="py-4 px-3 text-center">
          <button
            type="button"
            onclick="openAdminModal(${sub.id})"
            class="p-1 text-slate-700 hover:text-[#4e46e5] hover:scale-110 transition-all cursor-pointer"
            title="Lihat Detail & Review"
          >
            <i data-lucide="info" class="w-4.5 h-4.5"></i>
          </button>
        </td>
      </tr>
    `).join('');

    lucide.createIcons();
  }

  /**
   * Mengganti gambar preview utama di galeri modal review ketika kotak thumbnail diklik.
   * 
   * @param {HTMLElement} el Elemen kotak thumbnail yang diklik
   */
  function selectThumbImage(el) {
    const img = el.querySelector('img');
    if (img) {
      document.getElementById('modalPreviewImg').src = img.src;
    }
  }

  /**
   * Membuka modal review admin untuk pengajuan dengan ID tertentu.
   * Mem-populate data pemohon, detail merch, status, harga (mata uang & nominal), serta catatan seller.
   * 
   * @param {number} id ID pengajuan belanja
   */
  function openAdminModal(id) {
    const sub = adminSubmissionsList.find(s => s.id === id);
    if (!sub) return;

    selectedId = sub.id;
    document.getElementById('modalHeading').textContent = `#${sub.requestId || String(sub.id).padStart(6, '0')}`;
    document.getElementById('modalFoundAt').textContent = `Found ${sub.foundAt || '10/11/2026 10:08:55'}`;

    document.getElementById('infoUser').textContent = sub.submittedBy?.username || sub.submittedBy?.name || 'uccloud26';
    document.getElementById('infoFullName').textContent = sub.fullName || 'bukan manusia biasa';
    document.getElementById('infoAddress').textContent = sub.address || 'Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx';
    document.getElementById('infoPostalCode').textContent = sub.postalCode || '1945';
    document.getElementById('infoPhone').textContent = sub.phoneNumber || '0813 888 8888';

    document.getElementById('infoArtistBand').textContent = sub.artistBand || sub.title || 'Taylor Swift';
    document.getElementById('infoMerchType').textContent = sub.merchType || sub.category || 'T-Shirt';
    document.getElementById('infoReleasedYear').textContent = sub.releasedYear || '2016';
    document.getElementById('infoEdition').textContent = sub.merchEdition || 'Taylor Swift North American Tour';
    document.getElementById('infoDesc').textContent = sub.description || 'Taylor Swift North American Tour 2016, Long Sleeve, White with tour list';

    selectStatusOption(sub.status || 'In Progress');

    // Parse harga lama menjadi mata uang dan nominal terpisah
    const rawPrice = (sub.price && sub.price !== '-') ? String(sub.price).trim() : '';
    let detectedCurrency = sub.currency || 'IDR';
    let detectedAmount = '';

    if (rawPrice) {
      if (rawPrice.startsWith('$') || rawPrice.toUpperCase().includes('USD')) {
        detectedCurrency = 'USD';
      } else if (rawPrice.startsWith('€') || rawPrice.toUpperCase().includes('EUR')) {
        detectedCurrency = 'EUR';
      } else if (rawPrice.startsWith('£') || rawPrice.toUpperCase().includes('GBP')) {
        detectedCurrency = 'GBP';
      } else if (rawPrice.startsWith('¥') || rawPrice.toUpperCase().includes('JPY')) {
        detectedCurrency = 'JPY';
      } else if (rawPrice.startsWith('S$') || rawPrice.toUpperCase().includes('SGD')) {
        detectedCurrency = 'SGD';
      } else if (rawPrice.startsWith('A$') || rawPrice.toUpperCase().includes('AUD')) {
        detectedCurrency = 'AUD';
      } else if (rawPrice.startsWith('RM') || rawPrice.toUpperCase().includes('MYR')) {
        detectedCurrency = 'MYR';
      } else if (rawPrice.startsWith('Rp') || rawPrice.toUpperCase().includes('IDR')) {
        detectedCurrency = 'IDR';
      }

      // Ambil bagian angka numerik saja
      const numOnly = rawPrice.replace(/[^0-9]/g, '');
      if (numOnly) {
        detectedAmount = new Intl.NumberFormat('id-ID').format(numOnly);
      }
    } else if (sub.priceAmount) {
      detectedAmount = new Intl.NumberFormat('id-ID').format(String(sub.priceAmount).replace(/[^0-9]/g, ''));
    }

    const sym = currencySymbols[detectedCurrency] || 'Rp';
    selectCurrencyOption(detectedCurrency, sym);
    document.getElementById('modalPriceAmount').value = detectedAmount;
    updateModalPriceValue();

    document.getElementById('modalNotes').value = sub.sellerNotes && sub.sellerNotes !== '-' ? sub.sellerNotes : (sub.adminNotes || '-');

    const mainImg = sub.imageUrl || '/assets/poster_art.png';
    document.getElementById('modalPreviewImg').src = mainImg;
    document.getElementById('thumb1').src = mainImg;
    document.getElementById('thumb2').src = mainImg;
    document.getElementById('thumb3').src = mainImg;

    document.getElementById('adminReviewModal').classList.remove('hidden');
    lucide.createIcons();
  }

  /**
   * Menutup modal review admin dan mereset ID pengajuan terpilih.
   */
  function closeAdminModal() {
    document.getElementById('adminReviewModal').classList.add('hidden');
    selectedId = null;
  }

  /**
   * Mengirim hasil review pengajuan (Status baru, Harga tertera, Catatan) ke server backend
   * via endpoint PATCH /api/admin/submissions/{id}/status.
   */
  async function submitStatusUpdate() {
    if (!selectedId) return;

    const newStatus = document.getElementById('modalStatus').value;
    updateModalPriceValue();
    const curr = document.getElementById('modalCurrency').value;
    const nominal = document.getElementById('modalPriceAmount').value.trim();
    const price = document.getElementById('modalPrice').value.trim();
    const sellerNotes = document.getElementById('modalNotes').value.trim();

    const btn = document.getElementById('btnSaveModal');
    const label = document.getElementById('labelSaveModal');
    const spin = document.getElementById('spinSaveModal');

    btn.disabled = true;
    label.textContent = 'Saving...';
    spin.classList.remove('hidden');

    try {
      const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
      const response = await fetch(`/api/admin/submissions/${selectedId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: JSON.stringify({
          status: newStatus,
          price: price,
          currency: curr,
          priceAmount: nominal,
          price_amount: nominal,
          sellerNotes: sellerNotes,
          seller_notes: sellerNotes,
          adminNotes: sellerNotes,
          admin_notes: sellerNotes
        })
      });

      const res = await response.json();

      if (res.success) {
        closeAdminModal();
        loadAdminSubmissions();
        showToast(`Request #${selectedId} berhasil diperbarui.`);
      } else {
        alert(res.message || 'Gagal menyimpan status.');
      }
    } catch(e) {
      alert('Terjadi kesalahan jaringan.');
    } finally {
      btn.disabled = false;
      label.textContent = 'Save';
      spin.classList.add('hidden');
      lucide.createIcons();
    }
  }

  // Sync admin user info
  (function syncAdminInfo() {
    const authState = document.getElementById('adminAuthState');
    const unauthState = document.getElementById('adminUnauthState');
    const stored = localStorage.getItem('wm_user');
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    if (stored && token) {
      try {
        const u = JSON.parse(stored);
        if (u.role === 'admin' || (u.email && u.email.includes('admin'))) {
          if (u.name || u.username) document.getElementById('adminDropdownName').textContent = u.name || u.username;
          if (u.email) document.getElementById('adminDropdownEmail').textContent = u.email;
          if (authState && unauthState) {
            authState.classList.remove('hidden');
            unauthState.classList.add('hidden');
          }
          return;
        }
      } catch(e) {}
    }
    if (authState && unauthState) {
      authState.classList.add('hidden');
      unauthState.classList.remove('hidden');
    }
  })();

  loadAdminSubmissions();
</script>
@endsection

