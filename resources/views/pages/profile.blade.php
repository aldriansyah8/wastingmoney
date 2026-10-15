@extends('layouts.app')

@section('title', 'wastingmoney - Edit Profile')

@section('styles')
  <!-- Dedicated CSS for Profile Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/profile.css">
@endsection

@section('content')
<div class="profile-page-container selection:bg-indigo-100 selection:text-indigo-800">

  <!-- ================= TOP HEADER (Matching Image 1) ================= -->
  <header class="profile-header">
    <!-- Brand Logo -->
    <a href="/" class="flex items-center select-none cursor-pointer" title="Kembali ke Beranda">
      <img src="/assets/logo.png" alt="wastingmoney" class="profile-logo-img" />
    </a>

    <!-- Right Icons: Cart & Profile -->
    <div class="profile-right-nav">
      <a 
        href="/list" 
        class="hidden sm:inline-flex text-xs font-semibold text-slate-600 hover:text-[#4e46e5] transition-colors py-1 px-2.5 rounded-lg hover:bg-slate-50 items-center gap-1.5"
        title="Lihat Daftar Request"
      >
        <i data-lucide="layout-grid" class="w-4 h-4 text-[#4e46e5]"></i>
        <span>My Requests</span>
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
              <p id="profileDropdownName" class="text-sm font-bold text-slate-900 truncate">uccloud26</p>
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
              href="/list"
              class="w-full text-left px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors cursor-pointer block"
            >
              <i data-lucide="layout-grid" class="w-4 h-4 text-[#4e46e5]"></i>
              My Requests
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

  <!-- ================= MAIN EDIT PROFILE FORM (Matching Image 1) ================= -->
  <main class="profile-main-content">
    
    <form id="profileEditForm" class="profile-form" onsubmit="event.preventDefault(); promptConfirmSave();">
      @csrf

      <!-- 1. Username (Read-Only / Disabled Look) -->
      <div class="profile-form-group">
        <label for="profileUsername" class="profile-form-label">Username</label>
        <input 
          type="text" 
          id="profileUsername" 
          name="username" 
          value="uccloud26" 
          readonly 
          tabindex="-1"
          class="profile-input profile-input-readonly" 
        />
      </div>

      <!-- 2. Full Name -->
      <div class="profile-form-group">
        <label for="profileFullName" class="profile-form-label">Full Name</label>
        <input 
          type="text" 
          id="profileFullName" 
          name="full_name" 
          placeholder="Enter your fullname" 
          autocomplete="name"
          class="profile-input" 
        />
      </div>

      <!-- 3. Address -->
      <div class="profile-form-group">
        <label for="profileAddress" class="profile-form-label">Address</label>
        <input 
          type="text" 
          id="profileAddress" 
          name="address" 
          placeholder="Enter your full address" 
          autocomplete="street-address"
          class="profile-input" 
        />
      </div>

      <!-- 4. Phone Number & Postal Code (2 Columns) -->
      <div class="profile-grid-2-col">
        <div class="profile-form-group">
          <label for="profilePhone" class="profile-form-label">Phone Number</label>
          <input 
            type="text" 
            id="profilePhone" 
            name="phone_number" 
            placeholder="0813xxxxxxxx" 
            autocomplete="tel"
            class="profile-input" 
          />
        </div>

        <div class="profile-form-group">
          <label for="profilePostalCode" class="profile-form-label">Postal Code</label>
          <input 
            type="text" 
            id="profilePostalCode" 
            name="postal_code" 
            placeholder="Enter your postal code" 
            autocomplete="postal-code"
            class="profile-input" 
          />
        </div>
      </div>

      <!-- 5. Password & Change Password Link -->
      <div class="profile-form-group">
        <label for="profilePassword" class="profile-form-label">Password</label>
        <input 
          type="password" 
          id="profilePassword" 
          value="******" 
          readonly 
          tabindex="-1"
          class="profile-input profile-input-readonly" 
        />
        <a 
          href="javascript:void(0)" 
          class="profile-change-pwd-link" 
          onclick="togglePasswordDrawer()"
          id="togglePwdLink"
        >
          Change Password?
        </a>

        <!-- Expandable Password Change Drawer -->
        <div id="passwordDrawer" class="profile-password-drawer hidden">
          <div class="profile-form-group">
            <label class="text-xs font-semibold text-slate-700 mb-1">New Password</label>
            <input 
              type="password" 
              id="profileNewPassword" 
              placeholder="Enter new password (min. 6 characters)" 
              class="profile-input text-xs" 
            />
          </div>
          <div class="profile-form-group">
            <label class="text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
            <input 
              type="password" 
              id="profileConfirmPassword" 
              placeholder="Re-enter new password" 
              class="profile-input text-xs" 
            />
          </div>
        </div>
      </div>

      <!-- 6. Action Buttons: Save Changes & Cancel (Matching Image 1) -->
      <div class="profile-actions-row">
        <button 
          type="button" 
          class="profile-btn-save" 
          onclick="promptConfirmSave()"
        >
          Save Changes
        </button>

        <button 
          type="button" 
          class="profile-btn-cancel" 
          onclick="handleCancel()"
        >
          Cancel
        </button>
      </div>

    </form>

  </main>

  <!-- ================= CONFIRMATION MODAL (Matching Image 2) ================= -->
  <div 
    id="profileConfirmModalOverlay" 
    class="profile-confirm-overlay" 
    onclick="handleModalBackdropClick(event)"
    role="dialog" 
    aria-modal="true"
  >
    <div class="profile-confirm-card" onclick="event.stopPropagation()">
      <h3 class="profile-confirm-title">
        Are you sure want to save changes?
      </h3>

      <div class="profile-confirm-actions">
        <button 
          type="button" 
          class="profile-btn-yes" 
          onclick="executeSaveProfile()"
          id="btnConfirmYes"
        >
          Yes
        </button>

        <button 
          type="button" 
          class="profile-btn-modal-cancel" 
          onclick="closeConfirmModal()"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- ================= FOOTER (Matching Image 1) ================= -->
  <footer class="profile-footer">
    instagram &nbsp;|&nbsp; tiktok &nbsp;|&nbsp; threads &nbsp;|&nbsp; x
  </footer>

</div>
@endsection

@section('scripts')
<script>
  let currentUserData = {
    username: 'uccloud26',
    full_name: 'bukan manusia biasa',
    address: 'Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx',
    phone_number: '0813 888 8888',
    postal_code: '1945'
  };

  document.addEventListener('DOMContentLoaded', () => {
    loadProfileData();

    // Escape key listener for confirmation modal
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeConfirmModal();
    });
  });

  /**
   * Memuat data profil user aktif dari localStorage dan REST API backend (/api/auth/me).
   * Nilai yang didapat langsung dipopulasikan ke input form (Username, Full Name, Address, Phone, Postal Code).
   */
  async function loadProfileData() {
    // 1. Cek data tersimpan di localStorage browser
    const savedProfile = localStorage.getItem('wm_user_profile');
    if (savedProfile) {
      try {
        currentUserData = { ...currentUserData, ...JSON.parse(savedProfile) };
      } catch (err) {}
    }

    const userData = localStorage.getItem('wm_user_data') || localStorage.getItem('wm_admin_data');
    if (userData) {
      try {
        const u = JSON.parse(userData);
        if (u.name || u.username) currentUserData.username = u.name || u.username;
        if (u.email) currentUserData.email = u.email;
        if (u.full_name) currentUserData.full_name = u.full_name;
        if (u.address) currentUserData.address = u.address;
        if (u.phone_number) currentUserData.phone_number = u.phone_number;
        if (u.postal_code) currentUserData.postal_code = u.postal_code;

        document.getElementById('profileAuthState').classList.remove('hidden');
        document.getElementById('profileUnauthState').classList.add('hidden');
        document.getElementById('profileDropdownName').textContent = currentUserData.username;
        document.getElementById('profileDropdownEmail').textContent = u.email || 'user@wastingmoney.id';
      } catch (err) {}
    }

    // 2. Ambil data terbaru langsung dari endpoint backend Laravel
    try {
      const res = await fetch('/api/auth/me');
      const data = await res.json();
      if (data && data.success && data.user) {
        const u = data.user;
        currentUserData.username = u.username || u.name || currentUserData.username;
        if (u.full_name) currentUserData.full_name = u.full_name;
        if (u.address) currentUserData.address = u.address;
        if (u.phone_number) currentUserData.phone_number = u.phone_number;
        if (u.postal_code) currentUserData.postal_code = u.postal_code;
      }
    } catch (e) {
      console.log('Using local user profile state');
    }

    // 3. Masukkan nilai ke masing-masing elemen input di halaman
    document.getElementById('profileUsername').value = currentUserData.username || 'uccloud26';
    document.getElementById('profileFullName').value = currentUserData.full_name || '';
    document.getElementById('profileAddress').value = currentUserData.address || '';
    document.getElementById('profilePhone').value = currentUserData.phone_number || '';
    document.getElementById('profilePostalCode').value = currentUserData.postal_code || '';
  }

  /**
   * Menampilkan atau menyembunyikan drawer input ubah password saat link "Change Password?" diklik.
   */
  function togglePasswordDrawer() {
    const drawer = document.getElementById('passwordDrawer');
    const link = document.getElementById('togglePwdLink');
    if (drawer.classList.contains('hidden')) {
      drawer.classList.remove('hidden');
      link.textContent = 'Hide Password Form';
    } else {
      drawer.classList.add('hidden');
      link.textContent = 'Change Password?';
    }
  }

  /**
   * Memvalidasi form (kecocokan password baru) dan membuka modal pop-up konfirmasi
   * "Are you sure want to save changes?" sesuai desain mockup Image 2.
   */
  function promptConfirmSave() {
    // Validasi kecocokan konfirmasi password jika diisi
    const newPwd = document.getElementById('profileNewPassword').value;
    const confirmPwd = document.getElementById('profileConfirmPassword').value;
    if (newPwd && newPwd !== confirmPwd) {
      showToast('Konfirmasi password baru tidak cocok!');
      return;
    }

    const overlay = document.getElementById('profileConfirmModalOverlay');
    overlay.classList.add('active');
  }

  /**
   * Menutup modal pop-up konfirmasi penyimpanan profil.
   */
  function closeConfirmModal() {
    const overlay = document.getElementById('profileConfirmModalOverlay');
    overlay.classList.remove('active');
  }

  /**
   * Menutup modal konfirmasi saat pengguna mengklik latar belakang gelap.
   * 
   * @param {MouseEvent} e Event klik mouse
   */
  function handleModalBackdropClick(e) {
    if (e.target.id === 'profileConfirmModalOverlay') {
      closeConfirmModal();
    }
  }

  /**
   * Mengeksekusi penyimpanan data profil ke database backend (endpoint /api/auth/profile)
   * dan menyinkronkannya ke localStorage browser.
   */
  async function executeSaveProfile() {
    const btnYes = document.getElementById('btnConfirmYes');
    btnYes.disabled = true;
    btnYes.textContent = 'Saving...';

    const payload = {
      username: document.getElementById('profileUsername').value,
      full_name: document.getElementById('profileFullName').value,
      address: document.getElementById('profileAddress').value,
      phone_number: document.getElementById('profilePhone').value,
      postal_code: document.getElementById('profilePostalCode').value,
      new_password: document.getElementById('profileNewPassword').value
    };

    // Simpan segera ke localStorage browser
    localStorage.setItem('wm_user_profile', JSON.stringify(payload));

    // Perbarui data user aktif di sesi browser
    const existing = localStorage.getItem('wm_user_data');
    if (existing) {
      try {
        const u = JSON.parse(existing);
        u.full_name = payload.full_name;
        u.address = payload.address;
        u.phone_number = payload.phone_number;
        u.postal_code = payload.postal_code;
        localStorage.setItem('wm_user_data', JSON.stringify(u));
      } catch (err) {}
    }

    // Kirim payload pembaruan ke API backend Laravel
    try {
      const res = await fetch('/api/auth/profile', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || ''
        },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data && data.success) {
        showToast('Profil berhasil disimpan!');
      } else {
        showToast('Profil berhasil diperbarui secara lokal.');
      }
    } catch (e) {
      showToast('Profil berhasil disimpan.');
    } finally {
      btnYes.disabled = false;
      btnYes.textContent = 'Yes';
      closeConfirmModal();

      // Reset kolom input password
      document.getElementById('profileNewPassword').value = '';
      document.getElementById('profileConfirmPassword').value = '';
      document.getElementById('passwordDrawer').classList.add('hidden');
      document.getElementById('togglePwdLink').textContent = 'Change Password?';
    }
  }

  /**
   * Membatalkan perubahan yang belum disimpan dan mengembalikan form ke data tersimpan.
   */
  function handleCancel() {
    showToast('Perubahan dibatalkan.');
    loadProfileData();
  }

  /**
   * Menampilkan atau menyembunyikan menu pop-up dropdown user di header.
   */
  function toggleProfileDropdown() {
    const d = document.getElementById('userDropdown');
    d.classList.toggle('hidden');
  }

  // Menutup dropdown profil jika pengguna mengklik area luar
  document.addEventListener('click', (e) => {
    const wrapper = document.getElementById('userMenuWrapper');
    const d = document.getElementById('userDropdown');
    if (wrapper && !wrapper.contains(e.target) && d && !d.classList.contains('hidden')) {
      d.classList.add('hidden');
    }
  });

  /**
   * Menghapus sesi otentikasi user dan mengarahkan kembali ke halaman login.
   */
  function handleLogout() {
    localStorage.removeItem('wm_user_token');
    localStorage.removeItem('wm_user_data');
    showToast('Berhasil logout.');
    setTimeout(() => window.location.href = '/auth', 500);
  }
</script>
@endsection
