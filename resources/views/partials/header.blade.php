{{-- 
  wastingmoney - Reusable Header Partial
  File: resources/views/partials/header.blade.php
  
  Mendukung otomatis deteksi halaman (home, profile, user_list, admin, auth)
  atau dapat dipanggil secara spesifik: @include('partials.header', ['page' => 'home'])
--}}

@php
  $pageVariant = $page ?? (
    request()->is('admin*') ? 'admin' : (
      request()->is('cart*') || request()->is('list*') || request()->is('requests*') || request()->is('catalog*') ? 'user_list' : (
        request()->is('profile*') || request()->is('account*') ? 'profile' : (
          request()->is('auth*') || request()->is('login*') ? 'auth' : 'home'
        )
      )
    )
  );
  $isAdminSession = session('wm_user.role') === 'admin' || (session('wm_user.email') && str_contains(session('wm_user.email'), 'admin'));
@endphp

@if($pageVariant === 'auth')
  <!-- ================= TOP HEADER (Auth / Sign In / Sign Up) ================= -->
  <header class="auth-header">
    <div class="flex items-center select-none">
      <a href="/" class="flex items-center select-none cursor-pointer">
        <img 
          src="/assets/logo.png" 
          alt="wastingmoney" 
          class="auth-logo-img" 
        />
      </a>
    </div>
  </header>

@elseif($pageVariant === 'admin')
  <!-- ================= TOP HEADER (Admin Dashboard) ================= -->
  <header class="admin-header">
    <!-- Brand Logo -->
    <a href="/admin" class="flex items-center select-none cursor-pointer" title="Admin Dashboard">
      <img src="/assets/logo.png" alt="wastingmoney" class="admin-logo-img" />
    </a>

    <!-- Right Icons: Admin Profile (Shopping cart removed for Admin) -->
    <div class="flex items-center gap-4 text-slate-900 relative">
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

        <div id="adminDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50 animate-fadeIn">
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
              <i data-lucide="list" class="w-4 h-4 text-[#4e46e5]"></i>
              Daftar Request User
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

@elseif($pageVariant === 'user_list')
  <!-- ================= TOP HEADER (User Request Catalog / List) ================= -->
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

        <!-- Filter Popover Menu (Matching Foto 1: Queueing, In process, Found, Paid, Cancel) -->
        <div id="filterDropdownMenu" class="user-filter-popover hidden" onclick="event.stopPropagation()">
          <button type="button" class="user-filter-status-btn btn-status-queueing" onclick="selectStatusFilterDirect('Queue')">
            Queueing
          </button>
          <button type="button" class="user-filter-status-btn btn-status-process" onclick="selectStatusFilterDirect('Process')">
            In process
          </button>
          <button type="button" class="user-filter-status-btn btn-status-found" onclick="selectStatusFilterDirect('Found!')">
            Found
          </button>
          <button type="button" class="user-filter-status-btn btn-status-paid" onclick="selectStatusFilterDirect('Paid')">
            Paid
          </button>
          <button type="button" class="user-filter-status-btn btn-status-cancel" onclick="selectStatusFilterDirect('Cancel')">
            Cancel
          </button>
        </div>
      </div>

      <!-- Sort Chevrons Icon Button with Popover (Matching Foto 2: Artist Name, Request Date) -->
      <div class="user-sort-wrapper" id="sortDropdownWrapper">
        <button 
          type="button" 
          id="btnToggleSortDropdown"
          class="user-list-header-btn" 
          onclick="toggleSortDropdown()"
          title="Pilihan Urutan"
        >
          <i data-lucide="chevrons-up-down" class="w-5 h-5"></i>
        </button>

        <!-- Sort Popover Menu (Foto 2) -->
        <div id="sortDropdownMenu" class="user-sort-popover hidden" onclick="event.stopPropagation()">
          <button type="button" class="user-sort-btn-option" id="sortBtnArtist" onclick="selectSortOptionDirect('artist')">
            Artist Name
          </button>
          <button type="button" class="user-sort-btn-option" id="sortBtnDate" onclick="selectSortOptionDirect('date')">
            Request Date
          </button>
        </div>
      </div>
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

      @if(!$isAdminSession)
      <a 
        href="/cart" 
        class="header-cart-btn p-1 text-[#4e46e5] transition-colors cursor-pointer flex items-center"
        title="Keranjang Belanja"
      >
        <i data-lucide="shopping-cart" class="w-5 sm:w-6 h-5 sm:h-6"></i>
      </a>

      <span class="header-cart-divider text-slate-300 font-light select-none">|</span>
      @endif

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
              href="/cart"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="shopping-cart" class="w-4 h-4 text-[#4e46e5]"></i>
              Keranjang Belanja
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
              <p class="text-sm font-semibold text-slate-700">Mode Tamu / Default</p>
            </div>
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

@elseif($pageVariant === 'profile')
  <!-- ================= TOP HEADER (Edit Profile Page) ================= -->
  <header class="profile-header">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center select-none cursor-pointer" title="Kembali ke Beranda">
      <img src="/assets/logo.png" alt="wastingmoney" class="profile-logo-img" />
    </a>

    <!-- Right Icons: Cart & Profile (My Requests button removed as requested, Cart leads directly to Keranjang) -->
    <div class="profile-right-nav">
      @if(!$isAdminSession)
      <a 
        href="/cart" 
        class="header-cart-btn p-1 hover:text-[#4e46e5] transition-colors cursor-pointer flex items-center"
        title="Keranjang Belanja"
      >
        <i data-lucide="shopping-cart" class="w-5 sm:w-6 h-5 sm:h-6"></i>
      </a>

      <span class="header-cart-divider text-slate-300 font-light select-none">|</span>
      @endif

      <!-- User Menu with Dropdown -->
      <div class="relative" id="userMenuWrapper">
        <button 
          type="button" 
          onclick="toggleProfileDropdown()"
          class="p-1 rounded-full hover:text-[#4e46e5] transition-colors cursor-pointer flex items-center"
          title="Profile"
        >
          <div class="w-6 sm:w-7 h-6 sm:h-7 rounded-full border border-slate-900 flex items-center justify-center">
            <i data-lucide="user" class="w-4 sm:w-4.5 h-4 sm:h-4.5 text-slate-900"></i>
          </div>
        </button>

        <div id="userDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50 animate-fadeIn">
          <div id="profileAuthState" class="hidden">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Masuk sebagai:</p>
              <p id="profileDropdownName" class="text-sm font-bold text-slate-900 truncate">User</p>
              <p id="profileDropdownEmail" class="text-xs text-slate-500 truncate">user@wastingmoney.id</p>
            </div>
            <a
              href="/"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="plus" class="w-4 h-4"></i>
              Form Request Baru
            </a>
            <a
              href="/cart"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="shopping-cart" class="w-4 h-4 text-[#4e46e5]"></i>
              Keranjang Belanja
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

          <div id="profileUnauthState">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-xs text-slate-400 font-medium">Status Akun:</p>
              <p class="text-sm font-semibold text-slate-700">Mode Tamu / Default</p>
            </div>
            <a
              href="/auth"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-[#4e46e5] hover:bg-indigo-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="log-in" class="w-4 h-4"></i>
              Masuk / Login
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

@else
  <!-- ================= TOP HEADER (Home Page Default) ================= -->
  <header class="home-header">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center select-none cursor-pointer" title="Beranda">
      <img src="/assets/logo.png" alt="wastingmoney" class="home-logo-img" />
    </a>

    <!-- Right Icons: Cart & Profile -->
    <div class="flex items-center gap-4 text-slate-900 relative">
      @if(!$isAdminSession)
      <a 
        href="/cart" 
        class="header-cart-btn p-1 hover:text-[#4e46e5] transition-colors cursor-pointer flex items-center"
        title="Keranjang Belanja"
      >
        <i data-lucide="shopping-cart" class="w-5 sm:w-6 h-5 sm:h-6"></i>
      </a>

      <span class="header-cart-divider text-slate-300 font-light select-none">|</span>
      @endif

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
              href="/cart"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="shopping-cart" class="w-4 h-4 text-[#4e46e5]"></i>
              Keranjang Belanja
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
              href="/cart"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="shopping-cart" class="w-4 h-4 text-[#4e46e5]"></i>
              Keranjang Belanja
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
@endif
