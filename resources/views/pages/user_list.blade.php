@extends('layouts.app')

@section('title', 'wastingmoney - Merch Request List')

@section('styles')
  <!-- Dedicated CSS for User List Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/user_list.css?v={{ time() }}">
@endsection

@section('content')
<div class="user-list-page selection:bg-indigo-100 selection:text-indigo-800">

  <!-- Reusable Header Partial -->
  @include('partials.header')

  <!-- Filter Pills Bar (Disembunyikan secara default, filter dibuka melalui tombol filter di header) -->
  <div class="user-list-filter-bar hidden" id="filterPillsBar">
    <button type="button" class="user-list-filter-pill active" onclick="applyStatusFilter('all', this)">
      <span>Semua</span>
      <span class="text-xs opacity-70" id="countAll">(10)</span>
    </button>
    <button type="button" class="user-list-filter-pill" onclick="applyStatusFilter('Found!', this)">
      <span class="user-card-status-dot dot-green"></span>
      <span>Found!</span>
      <span class="text-xs opacity-70" id="countFound">(2)</span>
    </button>
    <button type="button" class="user-list-filter-pill" onclick="applyStatusFilter('Process', this)">
      <span class="user-card-status-dot dot-yellow"></span>
      <span>Process</span>
      <span class="text-xs opacity-70" id="countProcess">(2)</span>
    </button>
    <button type="button" class="user-list-filter-pill" onclick="applyStatusFilter('Queue', this)">
      <span class="user-card-status-dot dot-gray"></span>
      <span>Queue</span>
      <span class="text-xs opacity-70" id="countQueue">(1)</span>
    </button>
    <button type="button" class="user-list-filter-pill" onclick="applyStatusFilter('Cancel', this)">
      <span class="user-card-status-dot dot-pink"></span>
      <span>Cancel</span>
      <span class="text-xs opacity-70" id="countCancel">(2)</span>
    </button>
    <button type="button" class="user-list-filter-pill" onclick="applyStatusFilter('Paid', this)">
      <span class="user-card-status-dot dot-blue"></span>
      <span>Paid</span>
      <span class="text-xs opacity-70" id="countPaid">(3)</span>
    </button>
  </div>

  <!-- ================= MAIN CONTENT: 10 CARDS GRID (Matching Image 2 & 3) ================= -->
  <main class="user-list-main">
    
    <!-- Empty State -->
    <div id="emptyCatalogState" class="hidden text-center py-16">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
        <i data-lucide="search-x" class="w-8 h-8"></i>
      </div>
      <h3 class="text-base font-bold text-slate-800">Tidak ada request yang cocok</h3>
      <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci lain atau ubah filter status.</p>
    </div>

    <!-- Cards Grid Container -->
    <div class="user-list-grid" id="userCardsGrid">
      @for ($i = 0; $i < 6; $i++)
      <div class="user-card-skeleton">
        <div class="skeleton-img-wrap">
          <div class="shimmer-wave"></div>
          <i data-lucide="image" class="w-8 h-8 text-slate-300"></i>
        </div>
        <div class="skeleton-info">
          <div class="skeleton-pill"><div class="shimmer-wave"></div></div>
          <div class="skeleton-title"><div class="shimmer-wave"></div></div>
          <div class="skeleton-sub"><div class="shimmer-wave"></div></div>
        </div>
      </div>
      @endfor
    </div>
  </main>

  <!-- ================= MODAL DETAIL WITH DYNAMIC TEMPLATE COLOR (Matching Image 1 & 4) ================= -->
  <div 
    id="userItemModalOverlay" 
    class="user-modal-overlay" 
    onclick="handleBackdropClick(event)"
    role="dialog" 
    aria-modal="true"
  >
    <!-- Modal Card (Theme class applied dynamically: theme-green, theme-yellow, theme-gray, theme-pink, theme-blue) -->
    <div id="userModalCard" class="user-modal-card theme-green" onclick="event.stopPropagation()">
      
      <!-- Close Button (X in top right) -->
      <button 
        type="button" 
        class="user-modal-close-btn" 
        onclick="closeUserModal()"
        title="Tutup Modal (Esc)"
      >
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>

      <!-- Modal Two-Column Layout -->
      <div class="user-modal-layout">
        
        <!-- LEFT COLUMN: Product Gallery (1 Preview Utama di Atas, 2 Baris Foto: Foto Anda & Foto Admin) -->
        <div class="user-modal-gallery">
          <!-- 1 PREVIEW UTAMA DI ATAS -->
          <div class="user-modal-main-img-wrap" onclick="openUserPhotoLightbox(document.getElementById('userModalMainImg').src, 'Preview Foto')" title="Klik untuk memperbesar">
            <img 
              id="userModalMainImg" 
              src="/assets/placeholder_merch.svg" 
              alt="Foto Pengajuan Merch" 
              class="user-modal-main-img cursor-pointer" 
            />
          </div>

          <!-- BARIS FOTO 1: FOTO DARI ANDA -->
          <div class="user-modal-user-section mt-3">
            <div class="flex items-center justify-between mb-1.5 px-0.5">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                Foto dari Anda
              </span>
              <span class="text-[10px] text-slate-400 font-medium">Pengajuan Referensi</span>
            </div>
            <!-- Deretan Thumbnail Foto User -->
            <div class="user-modal-thumbs-row" id="userModalUserThumbsRow"></div>
          </div>

          <!-- BARIS FOTO 2: FOTO DARI ADMIN -->
          <div class="user-modal-admin-photos-section mt-3 pt-2.5 border-t border-slate-100 hidden" id="userModalAdminPhotosContainer">
            <div class="flex items-center justify-between mb-1.5 px-0.5">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                Foto dari Admin
              </span>
              <span class="text-[10px] text-slate-400 font-medium" id="userModalAdminPhotoCount">Barang Ditemukan</span>
            </div>

            <!-- Deretan Thumbnail Foto Admin -->
            <div class="user-modal-thumbs-row" id="userModalThumbsRow"></div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Information & Action Details (Image 1 & 4) -->
        <div class="user-modal-info">
          <div>
            <!-- Header Row: Request ID & Status Badge with Dot -->
            <div class="user-modal-header-row">
              <div>
                <div class="user-modal-req-label">Request ID</div>
                <div id="userModalReqId" class="user-modal-req-id">-</div>
              </div>

              <div class="user-modal-status-badge">
                <span id="userModalStatusDot" class="user-modal-status-dot dot-gray"></span>
                <span id="userModalStatusText">-</span>
              </div>
            </div>

            <!-- Artist / Band -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Artist/Band</span>
              <span id="userModalArtist" class="user-modal-field-value">-</span>
            </div>

            <!-- Merch Type & Released Year in 2 Columns -->
            <div class="user-modal-field-row-2">
              <div class="user-modal-field-group">
                <span class="user-modal-field-label">Merch Type</span>
                <span id="userModalMerchType" class="user-modal-field-value">-</span>
              </div>
              <div class="user-modal-field-group">
                <span class="user-modal-field-label">Released Year</span>
                <span id="userModalYear" class="user-modal-field-value">-</span>
              </div>
            </div>

            <!-- Merch Edition -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Merch Edition</span>
              <span id="userModalEdition" class="user-modal-field-value">-</span>
            </div>

            <!-- More Description -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">More Description</span>
              <span id="userModalDesc" class="user-modal-field-value">-</span>
            </div>

            <!-- Price -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Price</span>
              <span id="userModalPrice" class="user-modal-field-value">-</span>
            </div>

            <!-- Seller Notes -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Seller Notes</span>
              <span id="userModalSellerNotes" class="user-modal-field-value">-</span>
            </div>
          </div>

          <!-- Bottom Action Buttons Row (Image 1 & Image 4) -->
          <div class="user-modal-actions-row" id="userModalActionsArea">
            <!-- Dynamic Buttons rendered based on status template -->
          </div>

        </div>

      </div>

    </div>
  </div>

  <!-- ================= LIGHTBOX PHOTO PREVIEW MODAL ================= -->
  <div id="userPhotoLightboxModal" class="hidden user-modal-overlay z-[9999]" onclick="closeUserPhotoLightbox()">
    <div class="relative max-w-2xl w-full p-4 flex flex-col items-center" onclick="event.stopPropagation()">
      <div class="flex items-center justify-between w-full mb-2 px-2 text-white">
        <span id="userPhotoLightboxTitle" class="text-sm font-bold tracking-wide">Foto Preview</span>
        <button type="button" onclick="closeUserPhotoLightbox()" class="p-1 rounded-full bg-white/20 hover:bg-white/40 text-white transition cursor-pointer">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div class="bg-white rounded-2xl p-2 shadow-2xl max-h-[80vh] flex items-center justify-center overflow-hidden border border-white/20">
        <img id="userPhotoLightboxImg" src="" alt="Preview Full" class="max-h-[75vh] max-w-full object-contain rounded-xl" />
      </div>
    </div>
  </div>

  <!-- Reusable Footer Partial -->
  @include('partials.footer')

</div>
@endsection

@section('scripts')
<script src="/js/user_list.js?v={{ time() }}"></script>
@endsection
