@extends('layouts.app')

@section('title', 'wastingmoney - My Requests')

@section('styles')
  <!-- Dedicated CSS for User List Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/user_list.css">
@endsection

@section('content')
<div class="user-list-page selection:bg-indigo-100 selection:text-indigo-800">

  <!-- ================= TOP HEADER (Matching Image 2) ================= -->
  <header class="user-list-header">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center select-none cursor-pointer" title="Kembali ke Beranda">
      <img src="/assets/logo.png" alt="wastingmoney" class="user-list-logo-img" />
    </a>

    <!-- Center Section: Search Bar & Action Controls (Image 2) -->
    <div class="user-list-search-section">
      <div class="user-list-search-wrapper">
        <input 
          type="text" 
          id="userListSearchInput" 
          class="user-list-search-input" 
          placeholder="Search..."
          oninput="handleUserSearch(this.value)"
          autocomplete="off"
        />
      </div>

      <!-- Filter Funnel Icon Button with Interactive Popover -->
      <div class="user-filter-wrapper" id="filterDropdownWrapper">
        <button 
          type="button" 
          id="btnToggleFilterDropdown"
          class="user-list-header-btn" 
          onclick="toggleFilterDropdown()"
          title="Buka Pilihan Filter"
        >
          <i data-lucide="filter" class="w-5 h-5"></i>
          <span id="filterActiveDot" class="hidden user-filter-active-dot"></span>
        </button>

        <!-- Filter Popover Menu (Terbuka saat tombol filter diklik) -->
        <div id="filterDropdownMenu" class="user-filter-popover hidden" onclick="event.stopPropagation()">
          <div class="user-filter-popover-header">
            <span class="user-filter-popover-title">
              <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#4e46e5]"></i>
              Filter Request
            </span>
            <button type="button" class="user-filter-reset-btn" onclick="resetAllFilters()">
              Reset Semua
            </button>
          </div>

          <!-- Section 1: Status Pengajuan -->
          <div class="user-filter-section">
            <div class="user-filter-section-label">Status Pengajuan</div>
            <div class="user-filter-chips-grid">
              <button type="button" class="user-filter-chip active" data-status="all" onclick="selectStatusFilter('all', this)">
                Semua
              </button>
              <button type="button" class="user-filter-chip" data-status="Found!" onclick="selectStatusFilter('Found!', this)">
                <span class="user-card-status-dot dot-green"></span>
                Found!
              </button>
              <button type="button" class="user-filter-chip" data-status="Process" onclick="selectStatusFilter('Process', this)">
                <span class="user-card-status-dot dot-yellow"></span>
                Process
              </button>
              <button type="button" class="user-filter-chip" data-status="Queue" onclick="selectStatusFilter('Queue', this)">
                <span class="user-card-status-dot dot-gray"></span>
                Queue
              </button>
              <button type="button" class="user-filter-chip" data-status="Cancel" onclick="selectStatusFilter('Cancel', this)">
                <span class="user-card-status-dot dot-pink"></span>
                Cancel
              </button>
              <button type="button" class="user-filter-chip" data-status="Paid" onclick="selectStatusFilter('Paid', this)">
                <span class="user-card-status-dot dot-blue"></span>
                Paid
              </button>
            </div>
          </div>

          <!-- Section 2: Jenis Merchandise -->
          <div class="user-filter-section">
            <div class="user-filter-section-label">Jenis Merchandise</div>
            <div class="user-filter-chips-grid">
              <button type="button" class="user-filter-chip-type active" data-type="all" onclick="selectTypeFilter('all', this)">
                Semua Jenis
              </button>
              <button type="button" class="user-filter-chip-type" data-type="T-Shirt" onclick="selectTypeFilter('T-Shirt', this)">
                T-Shirt
              </button>
              <button type="button" class="user-filter-chip-type" data-type="Vinyl" onclick="selectTypeFilter('Vinyl', this)">
                Vinyl
              </button>
              <button type="button" class="user-filter-chip-type" data-type="CD" onclick="selectTypeFilter('CD', this)">
                CD
              </button>
              <button type="button" class="user-filter-chip-type" data-type="Hoodie" onclick="selectTypeFilter('Hoodie', this)">
                Hoodie
              </button>
              <button type="button" class="user-filter-chip-type" data-type="Jacket" onclick="selectTypeFilter('Jacket', this)">
                Jacket
              </button>
              <button type="button" class="user-filter-chip-type" data-type="Poster" onclick="selectTypeFilter('Poster', this)">
                Poster
              </button>
            </div>
          </div>

          <!-- Popover Footer Summary -->
          <div class="user-filter-popover-footer">
            <span class="user-filter-summary-text" id="filterSummaryText">Menampilkan 10 item</span>
            <button type="button" class="user-filter-close-btn" onclick="toggleFilterDropdown()">
              Selesai
            </button>
          </div>
        </div>
      </div>

      <!-- Sort Chevrons Icon Button -->
      <button 
        type="button" 
        class="user-list-header-btn" 
        onclick="toggleSortOrder()"
        title="Urutkan Request ID"
      >
        <i data-lucide="chevrons-up-down" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Right Navigation: Cart & User Profile -->
    <div class="user-list-right-nav">
      <a 
        href="/" 
        class="user-list-nav-link hidden md:inline-flex items-center gap-1.5"
        title="Buat Request Baru"
      >
        <i data-lucide="plus-circle" class="w-4 h-4 text-[#4e46e5]"></i>
        <span>Request Baru</span>
      </a>

      <button 
        type="button" 
        onclick="showToast('Keranjang belanja kosong.')"
        class="p-1 hover:text-[#4e46e5] transition-colors cursor-pointer"
        title="Cart"
      >
        <i data-lucide="shopping-cart" class="w-5 sm:w-6 h-5 sm:h-6"></i>
      </button>

      <span class="text-slate-300 font-light select-none">|</span>

      <!-- User Menu -->
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
          <div id="userAuthState" class="hidden">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Masuk sebagai:</p>
              <p id="userDropdownName" class="text-sm font-bold text-slate-900 truncate">User</p>
              <p id="userDropdownEmail" class="text-xs text-slate-500 truncate">user@wastingmoney.id</p>
            </div>
            <a
              href="/profile"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="user-cog" class="w-4 h-4 text-[#4e46e5]"></i>
              Edit Profile
            </a>
            <a
              href="/"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="plus" class="w-4 h-4"></i>
              Form Request Baru
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
              onclick="handleLogout()"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors cursor-pointer"
            >
              <i data-lucide="log-out" class="w-4 h-4"></i>
              Logout
            </button>
          </div>

          <div id="userUnauthState">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Status Akun:</p>
              <p class="text-sm font-semibold text-slate-700">Belum Masuk</p>
            </div>
            <a
              href="/auth"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-[#4e46e5] hover:bg-indigo-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="log-in" class="w-4 h-4"></i>
              Masuk / Login
            </a>
            <a
              href="/admin"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="shield" class="w-4 h-4"></i>
              Halaman Admin
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

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

  <!-- ================= FOOTER (Matching Image 1 & 2) ================= -->
  <footer class="user-list-footer">
    instagram &nbsp;|&nbsp; tiktok &nbsp;|&nbsp; threads &nbsp;|&nbsp; x
  </footer>

</div>
@endsection

@section('scripts')
<script>
  // 10 Cards Dataset: bentuk & warna tema status persis sesuai desain, namun bervariasi realistis (tidak urut kaku)
  const defaultUserRequests = [
    {
      id: 1,
      requestId: '#000001',
      artistBand: 'Taking Back Sunday',
      merchType: 'Vinyl',
      releasedYear: '2002',
      merchEdition: 'Tell All Your Friends 1st Press LP',
      description: 'Original first pressing vinyl record in mint condition with lyrical insert sheet',
      price: 'Rp 1.250.000',
      sellerNotes: 'Koleksi pribadi tersimpan rapi, cover dan piringan mulus tidak ada skip',
      status: 'Paid',
      statusDate: '12/10/2026 14:20:10',
      theme: 'theme-blue',
      dotClass: 'dot-blue',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 2,
      requestId: '#000002',
      artistBand: 'Taylor Swift',
      merchType: 'T-Shirt',
      releasedYear: '2016',
      merchEdition: 'North American Tour 2016 Commemorative',
      description: 'Taylor Swift North American Tour 2016, Long Sleeve, White with tour dates on back',
      price: 'Rp 800.000',
      sellerNotes: 'Kondisi B+ sangat terawat untuk kolektor',
      status: 'Found!',
      statusDate: '14/10/2026 09:15:32',
      theme: 'theme-green',
      dotClass: 'dot-green',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 3,
      requestId: '#000003',
      artistBand: 'Radiohead',
      merchType: 'Hoodie',
      releasedYear: '2000',
      merchEdition: 'Kid A Modified Bear Pullover',
      description: 'Vintage heavyweight black pullover hoodie with embroidered modified bear logo on chest',
      price: '-',
      sellerNotes: '-',
      status: 'Process',
      statusDate: '13/10/2026 18:40:05',
      theme: 'theme-yellow',
      dotClass: 'dot-yellow',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 4,
      requestId: '#000004',
      artistBand: 'Deftones',
      merchType: 'T-Shirt',
      releasedYear: '1997',
      merchEdition: 'Around The Fur Promo Vintage Tee',
      description: 'Vintage official promotional t-shirt washed black size XL single stitch original tag',
      price: '-',
      sellerNotes: '-',
      status: 'Queue',
      statusDate: '14/10/2026 11:05:12',
      theme: 'theme-gray',
      dotClass: 'dot-gray',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 5,
      requestId: '#000005',
      artistBand: 'The 1975',
      merchType: 'CD',
      releasedYear: '2013',
      merchEdition: 'Self-Titled Deluxe Edition Boxset',
      description: 'Limited edition deluxe 2-CD boxset includes bonus acoustic tracks and photo booklet',
      price: 'Rp 450.000',
      sellerNotes: 'Segel pabrik masih utuh (brand new factory sealed)',
      status: 'Found!',
      statusDate: '14/10/2026 16:30:20',
      theme: 'theme-green',
      dotClass: 'dot-green',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 6,
      requestId: '#000006',
      artistBand: 'Arctic Monkeys',
      merchType: 'Jacket',
      releasedYear: '2013',
      merchEdition: 'AM Tour Souvenir Bomber Jacket',
      description: 'Black satin bomber jacket with iconic AM soundwave embroidered on back, size L',
      price: '-',
      sellerNotes: '-',
      status: 'Cancel',
      statusDate: '11/10/2026 08:12:00',
      theme: 'theme-pink',
      dotClass: 'dot-pink',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 7,
      requestId: '#000007',
      artistBand: 'Nirvana',
      merchType: 'Poster',
      releasedYear: '1993',
      merchEdition: 'In Utero Original Promotional Tour Litho',
      description: 'Official 1993 Geffen promotional poster lithograph 24x36 inch rolled never pinned',
      price: 'Rp 650.000',
      sellerNotes: 'Kondisi prima tanpa lipatan, tanpa pin hole, kertas tebal',
      status: 'Paid',
      statusDate: '10/10/2026 21:00:15',
      theme: 'theme-blue',
      dotClass: 'dot-blue',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 8,
      requestId: '#000008',
      artistBand: 'My Chemical Romance',
      merchType: 'T-Shirt',
      releasedYear: '2006',
      merchEdition: 'The Black Parade Vintage Marching Tee',
      description: 'Original 2006 Black Parade world tour t-shirt size L faded black double sided print',
      price: '-',
      sellerNotes: '-',
      status: 'Process',
      statusDate: '13/10/2026 19:22:45',
      theme: 'theme-yellow',
      dotClass: 'dot-yellow',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 9,
      requestId: '#000009',
      artistBand: 'Oasis',
      merchType: 'Vinyl',
      releasedYear: '1995',
      merchEdition: '(What\'s the Story) Morning Glory? Double LP',
      description: 'Original Creation Records UK double vinyl LP gatefold sleeve collector grade',
      price: 'Rp 1.500.000',
      sellerNotes: 'VG+ condition collector quality, no scuffs',
      status: 'Paid',
      statusDate: '09/10/2026 12:45:00',
      theme: 'theme-blue',
      dotClass: 'dot-blue',
      image: '/assets/taylor_swift_shirt.png'
    },
    {
      id: 10,
      requestId: '#000010',
      artistBand: 'Frank Ocean',
      merchType: 'Hoodie',
      releasedYear: '2016',
      merchEdition: 'Blonded Tour Striped Crest Hoodie',
      description: 'Rare official Blonded tour hoodie navy green colorway premium french terry cotton',
      price: '-',
      sellerNotes: '-',
      status: 'Cancel',
      statusDate: '08/10/2026 17:10:30',
      theme: 'theme-pink',
      dotClass: 'dot-pink',
      image: '/assets/taylor_swift_shirt.png'
    }
  ];

  let currentRequests = [...defaultUserRequests];
  let activeStatusFilter = 'all';
  let activeTypeFilter = 'all';
  let activeSearchQuery = '';
  let sortAscending = true;
  let selectedRequestItem = null;

  // Initialize Page
  document.addEventListener('DOMContentLoaded', () => {
    checkUserAuth();
    loadLiveSubmissions();
    renderCards();
    updateFilterCounts();

    // Escape listener to close modal or filter popover
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeUserModal();
        closeFilterDropdown();
      }
    });

    // Menutup menu dropdown filter jika pengguna mengklik di luar area menu
    document.addEventListener('click', (e) => {
      const wrapper = document.getElementById('filterDropdownWrapper');
      const menu = document.getElementById('filterDropdownMenu');
      const btn = document.getElementById('btnToggleFilterDropdown');
      if (wrapper && !wrapper.contains(e.target) && menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        if (btn) btn.classList.remove('active');
      }
    });
  });

  /**
   * Memeriksa status otentikasi user dari localStorage.
   * Menampilkan nama dan email di menu profile header jika sudah login,
   * atau menampilkan tombol login jika berstatus guest (belum login).
   */
  function checkUserAuth() {
    const token = localStorage.getItem('wm_user_token') || localStorage.getItem('wm_admin_token');
    const userStr = localStorage.getItem('wm_user_data') || localStorage.getItem('wm_admin_data');
    const authState = document.getElementById('userAuthState');
    const unauthState = document.getElementById('userUnauthState');

    if (token && userStr) {
      try {
        const u = JSON.parse(userStr);
        document.getElementById('userDropdownName').textContent = u.name || 'User Wasting Money';
        document.getElementById('userDropdownEmail').textContent = u.email || 'user@wastingmoney.id';
        authState.classList.remove('hidden');
        unauthState.classList.add('hidden');
      } catch (err) {
        authState.classList.add('hidden');
        unauthState.classList.remove('hidden');
      }
    } else {
      authState.classList.add('hidden');
      unauthState.classList.remove('hidden');
    }
  }

  /**
   * Menampilkan atau menyembunyikan menu pop-up dropdown user di header.
   */
  function toggleUserDropdown() {
    const d = document.getElementById('userDropdown');
    d.classList.toggle('hidden');
  }

  // Menutup menu dropdown user jika pengguna mengklik di luar area menu
  document.addEventListener('click', (e) => {
    const wrapper = document.getElementById('userMenuWrapper');
    const d = document.getElementById('userDropdown');
    if (wrapper && !wrapper.contains(e.target) && d && !d.classList.contains('hidden')) {
      d.classList.add('hidden');
    }
  });

  /**
   * Menghapus sesi otentikasi (token dan data user) dan memuat ulang halaman.
   */
  function handleLogout() {
    localStorage.removeItem('wm_user_token');
    localStorage.removeItem('wm_user_data');
    showToast('Berhasil logout.');
    setTimeout(() => window.location.reload(), 500);
  }

  /**
   * Memuat request belanja baru yang dibuat user di form home secara realtime
   * dan menggabungkannya ke dalam daftar 10 kartu utama.
   */
  async function loadLiveSubmissions() {
    try {
      const userSubmissions = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');
      if (Array.isArray(userSubmissions) && userSubmissions.length > 0) {
        currentRequests = [...userSubmissions, ...defaultUserRequests];
        renderCards();
        updateFilterCounts();
      }
    } catch (e) {
      console.log('Using default client mockup requests data');
    }
  }

  /**
   * Me-render 10 kartu request ke dalam grid layout.
   * Menyesuaikan dengan filter status yang aktif, jenis merch, dan query pencarian pengguna.
   */
  function renderCards() {
    const grid = document.getElementById('userCardsGrid');
    const emptyState = document.getElementById('emptyCatalogState');
    if (!grid) return;

    let filtered = currentRequests.filter(item => {
      // Filter berdasarkan Status
      if (activeStatusFilter !== 'all' && item.status.toLowerCase() !== activeStatusFilter.toLowerCase()) {
        return false;
      }
      // Filter berdasarkan Jenis Merchandise
      if (activeTypeFilter !== 'all' && item.merchType.toLowerCase() !== activeTypeFilter.toLowerCase()) {
        return false;
      }
      // Filter berdasarkan Pencarian (Artist, Merch Type, Tahun, ID, Status, Edition)
      if (activeSearchQuery.trim()) {
        const q = activeSearchQuery.toLowerCase();
        const match = 
          item.artistBand.toLowerCase().includes(q) ||
          item.merchType.toLowerCase().includes(q) ||
          item.releasedYear.toLowerCase().includes(q) ||
          item.requestId.toLowerCase().includes(q) ||
          item.status.toLowerCase().includes(q) ||
          (item.merchEdition && item.merchEdition.toLowerCase().includes(q));
        if (!match) return false;
      }
      return true;
    });

    // Perbarui teks ringkasan di dalam popover filter
    const summaryText = document.getElementById('filterSummaryText');
    if (summaryText) {
      summaryText.textContent = `Menampilkan ${filtered.length} dari ${currentRequests.length} item`;
    }

    if (filtered.length === 0) {
      grid.innerHTML = '';
      emptyState.classList.remove('hidden');
      return;
    }

    emptyState.classList.add('hidden');
    grid.innerHTML = filtered.map(item => `
      <div 
        class="user-card-item ${item.theme}" 
        onclick="openUserModal(${item.id})"
        title="Klik untuk melihat detail & status request"
      >
        <!-- Product Image di dalam kontainer putih rounded -->
        <div class="user-card-img-wrap">
          <img 
            src="${item.image}" 
            alt="${item.artistBand} ${item.merchType}" 
            class="user-card-img" 
            onerror="this.onerror=null; this.src='/assets/taylor_swift_shirt.png';"
            loading="lazy"
          />
        </div>

        <!-- Detail di bagian bawah kartu: Tahun & Nama Barang -->
        <div class="user-card-info">
          <span class="user-card-year">${item.releasedYear}</span>
          <span class="user-card-title">${item.artistBand} | ${item.merchType}</span>
        </div>
      </div>
    `).join('');

    if (window.lucide) {
      lucide.createIcons();
    }
  }

  /**
   * Membuka modal detail item dengan warna latar belakang dinamis (Dynamic Template Color).
   * Warna modal disesuaikan otomatis dengan tema kartu yang diklik:
   * - Found! -> Tema Hijau (#bbf8b6) dengan tombol Pembayaran aktif
   * - Process -> Tema Kuning (#f6f7ae) dengan tombol Pembayaran disabled
   * - Queue -> Tema Abu-abu (#cecece) dengan tombol Pembayaran disabled
   * - Cancel -> Tema Pink/Merah (#f7aeae) dengan notifikasi dibatalkan
   * - Paid -> Tema Biru (#aed7f7) dengan notifikasi telah dibayar
   * 
   * @param {number} itemId ID dari item request yang diklik
   */
  function openUserModal(itemId) {
    const item = currentRequests.find(r => r.id === itemId);
    if (!item) return;

    selectedRequestItem = item;

    const overlay = document.getElementById('userItemModalOverlay');
    const card = document.getElementById('userModalCard');

    // 1. Terapkan warna tema dinamis ke modal card
    card.className = `user-modal-card ${item.theme}`;

    // 2. Isi gambar utama dan thumbnails
    const mainImg = document.getElementById('userModalMainImg');
    if (mainImg) {
      mainImg.src = item.image;
      mainImg.onerror = function() { this.src = '/assets/taylor_swift_shirt.png'; };
    }

    // Reset dan pasang gambar thumbnails
    const thumbs = card.querySelectorAll('.user-modal-thumb-box');
    thumbs.forEach((thumb, idx) => {
      const img = thumb.querySelector('img');
      if (img) {
        img.src = item.image;
        img.onerror = function() { this.src = '/assets/taylor_swift_shirt.png'; };
      }
      if (idx === 0) thumb.classList.add('selected');
      else thumb.classList.remove('selected');
    });

    // 3. Tampilkan data Request ID dan Status Badge
    document.getElementById('userModalReqId').textContent = item.requestId;
    
    const dotEl = document.getElementById('userModalStatusDot');
    dotEl.className = `user-modal-status-dot ${item.dotClass}`;
    
    document.getElementById('userModalStatusText').textContent = `${item.status} ${item.statusDate}`;

    // 4. Tampilkan rincian informasi barang
    document.getElementById('userModalArtist').textContent = item.artistBand;
    document.getElementById('userModalMerchType').textContent = item.merchType;
    document.getElementById('userModalYear').textContent = item.releasedYear;
    document.getElementById('userModalEdition').textContent = item.merchEdition;
    document.getElementById('userModalDesc').textContent = item.description;
    document.getElementById('userModalPrice').textContent = item.price;
    document.getElementById('userModalSellerNotes').textContent = item.sellerNotes;

    // 5. Render tombol aksi berdasarkan template status
    const actionsArea = document.getElementById('userModalActionsArea');
    if (actionsArea) {
      if (item.status === 'Found!') {
        // Status Hijau: Tombol Pembayaran Ungu Aktif + Tombol Cancel Merah
        actionsArea.innerHTML = `
          <button 
            type="button" 
            class="user-btn-pay-active" 
            onclick="handlePaymentClick('${item.requestId}', '${item.price}')"
          >
            Pembayaran
          </button>
          <button 
            type="button" 
            class="user-btn-cancel" 
            onclick="closeUserModal()"
          >
            Cancel
          </button>
        `;
      } else if (item.status === 'Queue' || item.status === 'Process') {
        // Status Kuning/Abu-abu: Tombol Pembayaran Nonaktif + Tombol Cancel Merah
        actionsArea.innerHTML = `
          <button 
            type="button" 
            class="user-btn-pay-disabled" 
            onclick="showToast('Barang belum ditemukan. Menunggu harga dari admin.')"
          >
            Pembayaran
          </button>
          <button 
            type="button" 
            class="user-btn-cancel" 
            onclick="closeUserModal()"
          >
            Cancel
          </button>
        `;
      } else if (item.status === 'Paid') {
        // Status Biru: Indikator sudah selesai dibayar
        actionsArea.innerHTML = `
          <div class="user-status-notice paid">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Sudah Selesai Dibayar</span>
          </div>
          <button 
            type="button" 
            class="user-btn-cancel" 
            onclick="closeUserModal()"
          >
            Tutup
          </button>
        `;
      } else if (item.status === 'Cancel') {
        // Status Pink: Indikator permintaan dibatalkan
        actionsArea.innerHTML = `
          <div class="user-status-notice canceled">
            <i data-lucide="x-circle" class="w-4 h-4"></i>
            <span>Permintaan Dibatalkan</span>
          </div>
          <button 
            type="button" 
            class="user-btn-cancel" 
            onclick="closeUserModal()"
          >
            Tutup
          </button>
        `;
      }
    }

    // 6. Buka overlay modal dan kunci scroll latar belakang
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (window.lucide) {
      lucide.createIcons();
    }
  }

  /**
   * Menutup modal pop-up detail dan mengaktifkan kembali scroll halaman.
   */
  function closeUserModal() {
    const overlay = document.getElementById('userItemModalOverlay');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  /**
   * Menutup modal ketika pengguna mengklik area backdrop gelap di luar kartu modal.
   * 
   * @param {MouseEvent} e Event klik mouse
   */
  function handleBackdropClick(e) {
    if (e.target.id === 'userItemModalOverlay') {
      closeUserModal();
    }
  }

  /**
   * Mengganti tampilan gambar utama di galeri modal ketika kotak thumbnail diklik.
   * 
   * @param {HTMLElement} el Elemen thumbnail yang diklik
   * @param {string} src URL sumber gambar
   */
  function switchModalThumb(el, src) {
    const mainImg = document.getElementById('userModalMainImg');
    if (mainImg) mainImg.src = src;

    const thumbs = document.querySelectorAll('.user-modal-thumb-box');
    thumbs.forEach(t => t.classList.remove('selected'));
    el.classList.add('selected');
  }

  /**
   * Menjalankan proses checkout / pembayaran ketika tombol "Pembayaran" diklik.
   * Mengupdate status item menjadi "Paid" dan mengubah tema warna kartu menjadi biru secara otomatis.
   * 
   * @param {string} reqId ID request barang (contoh: #000001)
   * @param {string} price Nominal harga yang harus dibayar
   */
  function handlePaymentClick(reqId, price) {
    showToast(`Mengalihkan ke gateway pembayaran untuk ${reqId} (${price})...`);
    setTimeout(() => {
      showToast(`Simulasi pembayaran ${price} berhasil diproses!`);
      // Update item to Paid
      if (selectedRequestItem) {
        selectedRequestItem.status = 'Paid';
        selectedRequestItem.theme = 'theme-blue';
        selectedRequestItem.dotClass = 'dot-blue';
        renderCards();
        openUserModal(selectedRequestItem.id);
      }
    }, 1200);
  }

  /**
   * Event handler untuk input pencarian (search bar).
   * Memfilter daftar kartu berdasarkan input kata kunci pengguna.
   * 
   * @param {string} val Kata kunci pencarian
   */
  function handleUserSearch(val) {
    activeSearchQuery = val;
    renderCards();
  }

  /**
   * Membuka atau menutup menu popover filter di header saat ikon corong filter diklik.
   */
  function toggleFilterDropdown() {
    const menu = document.getElementById('filterDropdownMenu');
    const btn = document.getElementById('btnToggleFilterDropdown');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
      menu.classList.remove('hidden');
      if (btn) btn.classList.add('active');
    } else {
      menu.classList.add('hidden');
      if (btn) btn.classList.remove('active');
    }
  }

  /**
   * Menutup menu popover filter di header.
   */
  function closeFilterDropdown() {
    const menu = document.getElementById('filterDropdownMenu');
    const btn = document.getElementById('btnToggleFilterDropdown');
    if (menu) menu.classList.add('hidden');
    if (btn) btn.classList.remove('active');
  }

  /**
   * Memilih opsi filter berdasarkan Status Pengajuan (Semua, Found!, Process, Queue, Cancel, Paid).
   * 
   * @param {string} status Nama status yang dipilih ('all', 'Found!', 'Process', 'Queue', 'Cancel', 'Paid')
   * @param {HTMLElement} btn Elemen tombol chip status yang diklik
   */
  function selectStatusFilter(status, btn) {
    activeStatusFilter = status;

    // Update styling chip status di dalam popover
    const chips = document.querySelectorAll('.user-filter-chip');
    chips.forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Sinkronkan juga pill bar jika ada
    const pills = document.querySelectorAll('.user-list-filter-pill');
    pills.forEach(p => {
      if (p.getAttribute('data-status') === status) {
        p.classList.add('active');
      } else {
        p.classList.remove('active');
      }
    });

    updateFilterBadge();
    renderCards();
  }

  /**
   * Memilih opsi filter berdasarkan Jenis Merchandise (Semua, T-Shirt, Vinyl, CD, Hoodie, Jacket, Poster).
   * 
   * @param {string} type Jenis merchandise yang dipilih ('all', 'T-Shirt', 'Vinyl', dll.)
   * @param {HTMLElement} btn Elemen tombol chip jenis merchandise yang diklik
   */
  function selectTypeFilter(type, btn) {
    activeTypeFilter = type;

    // Update styling chip jenis merchandise di dalam popover
    const chips = document.querySelectorAll('.user-filter-chip-type');
    chips.forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');

    updateFilterBadge();
    renderCards();
  }

  /**
   * Mengembalikan seluruh filter (Status dan Jenis Merchandise) ke kondisi default (Semua).
   */
  function resetAllFilters() {
    activeStatusFilter = 'all';
    activeTypeFilter = 'all';

    // Reset chips status di popover
    document.querySelectorAll('.user-filter-chip').forEach(c => {
      if (c.getAttribute('data-status') === 'all') c.classList.add('active');
      else c.classList.remove('active');
    });

    // Reset chips jenis merchandise di popover
    document.querySelectorAll('.user-filter-chip-type').forEach(c => {
      if (c.getAttribute('data-type') === 'all') c.classList.add('active');
      else c.classList.remove('active');
    });

    // Reset pill bar
    document.querySelectorAll('.user-list-filter-pill').forEach(p => {
      if (p.getAttribute('data-status') === 'all') p.classList.add('active');
      else p.classList.remove('active');
    });

    updateFilterBadge();
    renderCards();
    showToast('Semua filter berhasil direset.');
  }

  /**
   * Menampilkan atau menyembunyikan titik indikator aktif pada tombol filter header.
   */
  function updateFilterBadge() {
    const dot = document.getElementById('filterActiveDot');
    if (!dot) return;

    const isFiltered = (activeStatusFilter !== 'all' || activeTypeFilter !== 'all');
    if (isFiltered) {
      dot.classList.remove('hidden');
    } else {
      dot.classList.add('hidden');
    }
  }

  /**
   * Menerapkan filter status saat salah satu tombol pill (Semua, Found!, Process, dll.) diklik.
   * 
   * @param {string} status Nama status filter ('all', 'Found!', 'Process', 'Queue', 'Cancel', 'Paid')
   * @param {HTMLElement} btn Elemen tombol filter yang sedang aktif
   */
  function applyStatusFilter(status, btn) {
    selectStatusFilter(status, btn);
  }

  /**
   * Menampilkan atau menyembunyikan baris filter cepat (quick filter pills bar).
   */
  function toggleQuickFilters() {
    const bar = document.getElementById('filterPillsBar');
    if (bar) {
      bar.classList.toggle('hidden');
    }
  }

  /**
   * Membalik urutan daftar kartu antara No. 1 ke No. 10 (ascending) atau sebaliknya (descending).
   */
  function toggleSortOrder() {
    sortAscending = !sortAscending;
    currentRequests.sort((a, b) => {
      return sortAscending ? a.id - b.id : b.id - a.id;
    });
    renderCards();
    showToast(sortAscending ? 'Diurutkan dari No. 1' : 'Diurutkan dari terbaru');
  }

  /**
   * Menghitung jumlah item pada masing-masing status dan memperbarui teks badge filter.
   */
  function updateFilterCounts() {
    const countAll = currentRequests.length;
    const countFound = currentRequests.filter(r => r.status === 'Found!').length;
    const countProcess = currentRequests.filter(r => r.status === 'Process').length;
    const countQueue = currentRequests.filter(r => r.status === 'Queue').length;
    const countCancel = currentRequests.filter(r => r.status === 'Cancel').length;
    const countPaid = currentRequests.filter(r => r.status === 'Paid').length;

    const elAll = document.getElementById('countAll');
    if (elAll) elAll.textContent = `(${countAll})`;
    const elFound = document.getElementById('countFound');
    if (elFound) elFound.textContent = `(${countFound})`;
    const elProcess = document.getElementById('countProcess');
    if (elProcess) elProcess.textContent = `(${countProcess})`;
    const elQueue = document.getElementById('countQueue');
    if (elQueue) elQueue.textContent = `(${countQueue})`;
    const elCancel = document.getElementById('countCancel');
    if (elCancel) elCancel.textContent = `(${countCancel})`;
    const elPaid = document.getElementById('countPaid');
    if (elPaid) elPaid.textContent = `(${countPaid})`;
  }
</script>
@endsection
