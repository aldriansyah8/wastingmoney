@extends('layouts.app')

@section('title', 'wastingmoney - Admin')

@section('styles')
  <!-- Dedicated CSS for Admin Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/admin.css?v={{ time() }}">
  <link rel="stylesheet" href="/css/auth.css?v={{ time() }}">
@endsection

@section('content')
<div class="admin-page-container selection:bg-indigo-100 selection:text-indigo-800">
  
  <!-- Reusable Header Partial -->
  @include('partials.header')

  <!-- ================= MAIN CONTENT: REQUESTS TABLE (Matching admin_page1.png) ================= -->
  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 py-6 sm:py-10">
    
    <!-- Top Action Bar (Superadmin Add Admin button & Page Role Badge) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 sm:mb-6">
      <div class="flex items-center gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Admin Dashboard</h1>
      </div>

      <div class="flex items-center gap-3">
        <!-- Button Manage Account (Dapat diakses oleh Admin Biasa & Superadmin) -->
        <button
          type="button"
          id="btnOpenManageAccountsModal"
          onclick="openManageAccountsModal()"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:border-[#4e46e5] text-slate-700 hover:text-[#4e46e5] text-xs sm:text-sm font-bold shadow-xs transition-all cursor-pointer active:scale-95"
          title="Kelola Akun (Manage Accounts)"
        >
          <i data-lucide="users" class="w-4 h-4 text-[#4e46e5]"></i>
          <span>Manage Account</span>
        </button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="admin-table-wrapper relative">
      <div id="adminTableLoadingBar" class="admin-table-loading-bar"></div>
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
            <th class="py-4 px-4 text-center"></th>
          </tr>
        </thead>

        <tbody id="adminTableBody">
          @for ($i = 0; $i < 6; $i++)
          <tr class="admin-skeleton-row">
            <td class="py-4 px-3"><div class="admin-skeleton-cell h-4 w-4"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-16"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-24"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-36"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-16"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-12"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-6 w-20 rounded-full"></div></td>
            <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-20"></div></td>
            <td class="py-4 px-4 text-center"><div class="admin-skeleton-cell h-6 w-16 mx-auto"></div></td>
          </tr>
          @endfor
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
        <!-- LEFT COLUMN: Product Gallery (Hanya 1 Preview di Atas, 2 Baris Foto: Foto User & Foto Admin) -->
        <div class="admin-modal-gallery">
          <!-- 1 PREVIEW UTAMA DI ATAS (Persis Mockup SS 1) -->
          <div class="admin-modal-single-preview-section">
            <div 
              class="admin-modal-main-img-wrap" 
              onclick="openPhotoLightbox(document.getElementById('modalMainPreviewImg')?.src || document.getElementById('modalUserImg')?.src, 'Preview Foto')"
              title="Klik untuk memperbesar foto"
            >
              <img id="modalMainPreviewImg" src="/assets/poster_art.png" alt="Preview Foto" class="admin-modal-main-img" />
              <!-- Backward compatibility element references -->
              <img id="modalUserImg" src="/assets/poster_art.png" alt="Foto dari User" class="hidden" />
              <img id="modalPreviewImg" src="/assets/poster_art.png" alt="Preview" class="hidden" />
              <div class="zoom-hint">
                <i data-lucide="zoom-in" class="w-3.5 h-3.5"></i>
                <span>Perbesar</span>
              </div>
            </div>
          </div>

          <!-- BARIS FOTO 1: FOTO DARI USER -->
          <div class="admin-modal-user-section mt-3.5">
            <div class="admin-gallery-section-label">
              <span class="flex items-center gap-1.5 font-bold text-slate-800">
                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-500"></i>
                Foto dari User
              </span>
              <span class="tag">Pengajuan Referensi</span>
            </div>
            <!-- Deretan Thumbnail Foto User -->
            <div class="admin-modal-admin-photos-row" id="adminModalUserPhotosRow">
              <!-- Rendered via renderAdminGallery() in admin.js -->
            </div>
          </div>

          <!-- BARIS FOTO 2: FOTO DARI ADMIN -->
          <div class="admin-modal-admin-photos-section mt-3 pt-2.5 border-t border-slate-100">
            <div class="admin-gallery-section-label">
              <span class="flex items-center gap-1.5 font-bold text-slate-800">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                Foto dari Admin
              </span>
              <span class="tag" id="adminPhotoCountTag">(0/3 Foto)</span>
            </div>

            <!-- Deretan Thumbnail Foto Admin & Tombol Upload Max 3 Photo (Persis Mockup SS 2) -->
            <div class="admin-modal-admin-photos-row" id="adminModalAdminPhotosRow">
              <!-- Rendered via renderAdminGallery() in admin.js -->
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
              <span id="modalTopStatusDot" class="wm-status-dot wm-status-dot-process"></span>
              <span id="modalTopStatusText" class="font-bold text-slate-800">In Progress</span>
              <span id="modalFoundAt" class="text-slate-500 font-normal">10/11/2026 10:08:55</span>
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
                <div class="wm-dropdown-item" data-value="Queue" onclick="selectStatusOption('Queue')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-queue"></span>
                    <span>Queue (Abu-abu / User Request)</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item" data-value="Process" onclick="selectStatusOption('Process')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-process"></span>
                    <span>Process / In Progress (Kuning / Sedang Diproses)</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item selected" data-value="Found!" onclick="selectStatusOption('Found!')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-found"></span>
                    <span>Found! (Hijau / Disetujui Admin)</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item" data-value="Paid" onclick="selectStatusOption('Paid')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-paid"></span>
                    <span>Paid (Biru / User Lanjut)</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
                <div class="wm-dropdown-item" data-value="Cancel" onclick="selectStatusOption('Cancel')">
                  <div class="wm-dropdown-item-left">
                    <span class="wm-status-dot wm-status-dot-cancel"></span>
                    <span>Cancel (Merah / Dibatalkan)</span>
                  </div>
                  <i data-lucide="check" class="wm-dropdown-check"></i>
                </div>
              </div>
              <input type="hidden" id="modalStatus" value="Found!" />
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

          <!-- Action Buttons: Save Changes (Blue) & Cancel (Red) -->
          <div class="admin-modal-actions">
            <button
              type="button"
              id="btnSaveModal"
              onclick="promptConfirmAdminSave()"
              class="admin-btn-save-modal"
            >
              <span id="labelSaveModal">Save Changes</span>
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

  <!-- ================= CONFIRMATION MODAL ("Are you sure want to save changes?") ================= -->
  <div 
    id="adminConfirmModalOverlay" 
    class="admin-confirm-overlay" 
    onclick="handleAdminConfirmBackdrop(event)"
    role="dialog" 
    aria-modal="true"
  >
    <div class="admin-confirm-card" onclick="event.stopPropagation()">
      <h3 class="admin-confirm-title">
        Are you sure want to save changes?
      </h3>

      <div class="admin-confirm-actions">
        <button 
          type="button" 
          class="admin-btn-confirm-yes" 
          onclick="executeAdminSave()"
          id="btnAdminConfirmYes"
        >
          Yes
        </button>

        <button 
          type="button" 
          class="admin-btn-confirm-cancel" 
          onclick="closeAdminConfirmModal()"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- ================= CONFIRMATION MODAL UNTUK HAPUS REQUEST DARI USER ================= -->
  <div 
    id="adminDeleteConfirmOverlay" 
    class="admin-confirm-overlay" 
    onclick="handleAdminDeleteConfirmBackdrop(event)"
    role="dialog" 
    aria-modal="true"
  >
    <div class="admin-confirm-card" onclick="event.stopPropagation()">
      <h3 class="admin-confirm-title text-slate-900">
        Apakah Anda yakin ingin menghapus permintaan <span id="deleteSubmissionRequestIdText" class="text-rose-600 font-mono font-bold">#0000000</span>?
      </h3>
      <p class="text-xs sm:text-sm text-slate-500 -mt-5 mb-6">
        Tindakan ini bersifat permanen. Seluruh data pengajuan dan katalog terkait akan dihapus dari sistem dan database.
      </p>

      <div class="admin-confirm-actions">
        <button 
          type="button" 
          class="admin-btn-confirm-yes bg-rose-600 hover:bg-rose-700 shadow-rose-200" 
          onclick="executeDeleteSubmission()"
          id="btnConfirmDeleteSubmission"
        >
          Ya, Hapus
        </button>

        <button 
          type="button" 
          class="admin-btn-confirm-cancel bg-slate-200 text-slate-700 hover:bg-slate-300 shadow-none" 
          onclick="closeAdminDeleteConfirmModal()"
        >
          Batal
        </button>
      </div>
    </div>
  </div>

  <!-- ================= MODAL MANAGE ACCOUNTS (SUPERADMIN ONLY) ================= -->
  <div id="superadminManageAccountsModal" class="hidden admin-modal-backdrop" onclick="handleAdminModalBackdrop(event)">
    <div class="admin-accounts-card" onclick="event.stopPropagation()">
      
      <!-- Close Button (Matching Project Admin Modal Standard) -->
      <button
        type="button"
        onclick="closeManageAccountsModal()"
        class="admin-modal-close-btn"
        title="Tutup Modal"
      >
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>

      <!-- Modal Header (Matching Project Header Design) -->
      <div class="admin-accounts-header">
        <div>
          <div class="admin-accounts-title-group">
            <h2 class="admin-accounts-title select-none cursor-default">Manage Accounts</h2>
          </div>
          <p class="admin-accounts-subtitle">Kelola akun Pengguna (User) dan Administrator dalam sistem</p>
        </div>
      </div>

      <!-- Controls & Filter Toolbar (Matching Project Toolbar Standard) -->
      <div class="admin-accounts-toolbar">
        <!-- Search Input -->
        <div class="admin-accounts-search-wrap">
          <i data-lucide="search" class="admin-accounts-search-icon"></i>
          <input
            type="text"
            id="manageAccountsSearch"
            oninput="handleAccountsSearch(this.value)"
            placeholder="Cari nama atau email akun..."
            class="admin-accounts-search-input"
          />
        </div>

        <!-- Filter Tabs & Add Admin Button -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <div class="admin-accounts-filter-tabs">
            <button
              type="button"
              id="filterTabAll"
              onclick="filterAccountsByRole('all')"
              class="admin-tab-btn active"
            >
              Semua (<span id="countTabAll">0</span>)
            </button>
            <button
              type="button"
              id="filterTabAdmin"
              onclick="filterAccountsByRole('admin')"
              class="admin-tab-btn"
            >
              Admin (<span id="countTabAdmin">0</span>)
            </button>
            <button
              type="button"
              id="filterTabUser"
              onclick="filterAccountsByRole('user')"
              class="admin-tab-btn"
            >
              User (<span id="countTabUser">0</span>)
            </button>
          </div>

          <button
            type="button"
            id="btnAddAdminInline"
            onclick="openAddAdminModal()"
            class="admin-btn-add-admin-inline"
            title="Tambah Admin Baru"
          >
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
            <span>+ Tambah Admin</span>
          </button>
        </div>
      </div>

      <!-- Feedback Alert inside Modal -->
      <div id="manageAccountsAlert" class="hidden mx-6 mt-3 p-3 rounded-xl text-xs flex items-center justify-between gap-2 border">
        <span id="manageAccountsAlertMsg"></span>
        <button type="button" onclick="dismissAccountsAlert()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
          <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
      </div>

      <!-- Table Container (Using .admin-table matching admin dashboard) -->
      <div class="admin-accounts-body">
        <div class="admin-table-wrapper border border-slate-200 rounded-xl overflow-hidden">
          <table class="admin-table">
            <thead>
              <tr>
                <th class="py-3 px-3 text-center w-12">
                  <div class="th-content justify-center">No <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i></div>
                </th>
                <th class="py-3 px-4">
                  <div class="th-content">Nama Pengguna <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i></div>
                </th>
                <th class="py-3 px-4">
                  <div class="th-content">Email <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i></div>
                </th>
                <th class="py-3 px-4">
                  <div class="th-content">Role <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i></div>
                </th>
                <th class="py-3 px-4 text-center">
                  <div class="th-content justify-center">Aksi / Ubah Role</div>
                </th>
              </tr>
            </thead>
            <tbody id="manageAccountsTableBody">
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-400">
                  <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto text-[#4e46e5] mb-2"></i>
                  Memuat daftar akun...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Footer (Matching Project Standard) -->
      <div class="admin-accounts-footer">
        <span id="manageAccountsSummaryText">Total 0 akun terdaftar</span>
        <button
          type="button"
          onclick="closeManageAccountsModal()"
          class="admin-btn-close-footer"
        >
          Tutup
        </button>
      </div>

    </div>
  </div>

  <!-- ================= MODAL TAMBAH ADMIN BARU (Khusus Superadmin - Format Style Register User) ================= -->
  <div id="superadminAddAdminModal" class="hidden admin-modal-backdrop" onclick="handleAdminModalBackdrop(event)">
    <div class="auth-form-card relative max-w-lg mx-auto bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fadeIn" onclick="event.stopPropagation()">
      <!-- Close Button -->
      <button
        type="button"
        onclick="closeAddAdminModal()"
        class="absolute top-5 right-5 p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
        title="Tutup Modal"
      >
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>

      <!-- Modal Header (Style Register User) -->
      <div class="mb-6">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-indigo-50 text-[#4e46e5] mb-2">
          <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#4e46e5]"></i>
          Fitur Khusus Superadmin
        </div>
        <h2 class="auth-card-title text-2xl font-bold text-slate-900">
          Register New Admin
        </h2>
        <p class="auth-card-subtitle text-sm text-slate-600 mt-1">
          Daftarkan akun administrator untuk <strong class="font-extrabold text-slate-900">wasting money.</strong>
        </p>
      </div>

      <!-- Feedback Alerts -->
      <div id="addAdminErrorAlert" class="hidden mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-start gap-2.5">
        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
        <span id="addAdminErrorMsg">Pesan kesalahan</span>
      </div>

      <div id="addAdminSuccessAlert" class="hidden mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
        <span id="addAdminSuccessMsg">Admin baru berhasil didaftarkan!</span>
      </div>

      <!-- Form Tambah Admin (Format Mengikuti Register User) -->
      <form id="addAdminForm" class="space-y-4" onsubmit="event.preventDefault(); submitCreateAdmin();">
        @csrf

        <!-- Email Field -->
        <div>
          <label class="auth-input-label" for="newAdminEmail">Email</label>
          <input
            type="email"
            id="newAdminEmail"
            name="email"
            placeholder="admin@wastingmoney.id"
            required
            autocomplete="email"
            class="auth-text-input"
          />
        </div>

        <!-- Username Field -->
        <div>
          <label class="auth-input-label" for="newAdminUsername">Username</label>
          <input
            type="text"
            id="newAdminUsername"
            name="username"
            placeholder="Enter admin username"
            required
            autocomplete="username"
            class="auth-text-input"
          />
        </div>

        <!-- Input Hidden Role: Selalu Admin Biasa (Superadmin sistem hanya 1 dan tidak dapat ditambah) -->
        <input type="hidden" id="newAdminRole" name="role" value="admin" />

        <!-- Password Field with View Password Toggle -->
        <div>
          <label class="auth-input-label" for="newAdminPassword">Password</label>
          <div class="relative">
            <input
              type="password"
              id="newAdminPassword"
              name="password"
              placeholder="Enter admin password (min. 6 characters)"
              required
              autocomplete="new-password"
              class="auth-text-input pr-11"
            />
            <button
              type="button"
              onclick="togglePassword('newAdminPassword', 'eyeIconNewAdminPwd')"
              class="auth-password-toggle-btn"
              tabindex="-1"
              title="Lihat / Sembunyikan Password"
            >
              <i id="eyeIconNewAdminPwd" data-lucide="eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- Re-type Password Field with View Password Toggle -->
        <div>
          <label class="auth-input-label" for="newAdminConfirmPassword">Re-type Password</label>
          <div class="relative">
            <input
              type="password"
              id="newAdminConfirmPassword"
              placeholder="Re-type admin password"
              required
              autocomplete="new-password"
              class="auth-text-input pr-11"
            />
            <button
              type="button"
              onclick="togglePassword('newAdminConfirmPassword', 'eyeIconNewAdminConfirm')"
              class="auth-password-toggle-btn"
              tabindex="-1"
              title="Lihat / Sembunyikan Password"
            >
              <i id="eyeIconNewAdminConfirm" data-lucide="eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 flex items-center gap-3">
          <button
            type="submit"
            id="btnAddAdminSubmit"
            class="auth-submit-btn flex-1"
          >
            <span id="labelAddAdmin">Daftarkan Admin</span>
            <span id="spinAddAdmin" class="hidden">
              <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
            </span>
          </button>
          <button
            type="button"
            onclick="closeAddAdminModal()"
            class="px-5 py-3 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition-colors cursor-pointer text-xs sm:text-sm"
          >
            Batal
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= LIGHTBOX PHOTO PREVIEW MODAL ================= -->
  <div id="photoLightboxModal" class="hidden admin-modal-backdrop z-[9999]" onclick="closePhotoLightbox()">
    <div class="relative max-w-2xl w-full p-4 flex flex-col items-center" onclick="event.stopPropagation()">
      <div class="flex items-center justify-between w-full mb-2 px-2 text-white">
        <span id="photoLightboxTitle" class="text-sm font-bold tracking-wide">Foto Preview</span>
        <button type="button" onclick="closePhotoLightbox()" class="p-1 rounded-full bg-white/20 hover:bg-white/40 text-white transition cursor-pointer">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div class="bg-white rounded-2xl p-2 shadow-2xl max-h-[80vh] flex items-center justify-center overflow-hidden border border-white/20">
        <img id="photoLightboxImg" src="" alt="Preview Full" class="max-h-[75vh] max-w-full object-contain rounded-xl" />
      </div>
    </div>
  </div>

  <!-- Reusable Footer Partial -->
  @include('partials.footer')
</div>
@endsection

@section('scripts')
<script src="/js/admin.js?v={{ time() }}"></script>
@endsection
