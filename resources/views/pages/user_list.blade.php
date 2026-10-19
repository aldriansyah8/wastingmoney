@extends('layouts.app')

@section('title', 'wastingmoney - Keranjang Belanja')

@section('styles')
  <!-- Dedicated CSS for User List Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/user_list.css">
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
      <!-- Cards rendered dynamically via JavaScript based on 5 template themes -->
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
        
        <!-- LEFT COLUMN: Product Gallery (Image 1 & 4) -->
        <div class="user-modal-gallery">
          <div class="user-modal-main-img-wrap">
            <img 
              id="userModalMainImg" 
              src="/assets/taylor_swift_shirt.png" 
              alt="Merch Image" 
              class="user-modal-main-img" 
            />
          </div>

          <!-- Thumbnails Row -->
          <div class="user-modal-thumbs-row">
            <div class="user-modal-thumb-box selected" onclick="switchModalThumb(this, '/assets/taylor_swift_shirt.png')">
              <img src="/assets/taylor_swift_shirt.png" alt="Thumb 1" />
            </div>
            <div class="user-modal-thumb-box" onclick="switchModalThumb(this, '/assets/taylor_swift_shirt.png')">
              <img src="/assets/taylor_swift_shirt.png" alt="Thumb 2" />
            </div>
            <div class="user-modal-thumb-box" onclick="switchModalThumb(this, '/assets/taylor_swift_shirt.png')">
              <img src="/assets/taylor_swift_shirt.png" alt="Thumb 3" />
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Information & Action Details (Image 1 & 4) -->
        <div class="user-modal-info">
          <div>
            <!-- Header Row: Request ID & Status Badge with Dot -->
            <div class="user-modal-header-row">
              <div>
                <div class="user-modal-req-label">Request ID</div>
                <div id="userModalReqId" class="user-modal-req-id">#000001</div>
              </div>

              <div class="user-modal-status-badge">
                <span id="userModalStatusDot" class="user-modal-status-dot dot-green"></span>
                <span id="userModalStatusText">Found! 10/11/2026 10:08:55</span>
              </div>
            </div>

            <!-- Artist / Band -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Artist/Band</span>
              <span id="userModalArtist" class="user-modal-field-value">Taylor Swift</span>
            </div>

            <!-- Merch Type & Released Year in 2 Columns -->
            <div class="user-modal-field-row-2">
              <div class="user-modal-field-group">
                <span class="user-modal-field-label">Merch Type</span>
                <span id="userModalMerchType" class="user-modal-field-value">T-Shirt</span>
              </div>
              <div class="user-modal-field-group">
                <span class="user-modal-field-label">Released Year</span>
                <span id="userModalYear" class="user-modal-field-value">2016</span>
              </div>
            </div>

            <!-- Merch Edition -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Merch Edition</span>
              <span id="userModalEdition" class="user-modal-field-value">Taylor Swift North American Tour</span>
            </div>

            <!-- More Description -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">More Description</span>
              <span id="userModalDesc" class="user-modal-field-value">Taylor Swift North American Tour 2016, Long Sleeve, White with tour list</span>
            </div>

            <!-- Price -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Price</span>
              <span id="userModalPrice" class="user-modal-field-value">Rp 800.000</span>
            </div>

            <!-- Seller Notes -->
            <div class="user-modal-field-group">
              <span class="user-modal-field-label">Seller Notes</span>
              <span id="userModalSellerNotes" class="user-modal-field-value">B Condition suitable for collectors</span>
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

  <!-- Reusable Footer Partial -->
  @include('partials.footer')

</div>
@endsection

@section('scripts')
<script src="/js/user_list.js"></script>
@endsection
