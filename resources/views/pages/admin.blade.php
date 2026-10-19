@extends('layouts.app')

@section('title', 'wastingmoney - Admin')

@section('styles')
  <!-- Dedicated CSS for Admin Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/admin.css">
@endsection

@section('content')
<div class="admin-page-container selection:bg-indigo-100 selection:text-indigo-800">
  
  <!-- Reusable Header Partial -->
  @include('partials.header')

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
                    <span>Process (Kuning / Tunggu Approve)</span>
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

  <!-- Reusable Footer Partial -->
  @include('partials.footer')
</div>
@endsection

@section('scripts')
<script src="/js/admin.js"></script>
@endsection
