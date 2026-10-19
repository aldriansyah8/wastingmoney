/**
 * wastingmoney - Global Application & Authentication Manager
 * File: public/js/app.js
 */

// Inisialisasi ikon Lucide
if (window.lucide) {
  lucide.createIcons();
}

/**
 * Mendapatkan objek data pengguna yang sedang aktif (dari localStorage atau sesi server).
 * 
 * @returns {Object|null} Objek user aktif
 */
function getActiveUser() {
  const stored = localStorage.getItem('wm_user');
  if (stored) {
    try {
      return JSON.parse(stored);
    } catch(e) {}
  }
  const metaUser = document.querySelector('meta[name="server-user"]');
  if (metaUser && metaUser.content && metaUser.content !== 'null' && metaUser.content !== '') {
    try {
      const u = JSON.parse(metaUser.content);
      if (u) {
        localStorage.setItem('wm_user', JSON.stringify(u));
        return u;
      }
    } catch(e) {}
  }
  if (window.__SERVER_USER__) {
    try {
      localStorage.setItem('wm_user', JSON.stringify(window.__SERVER_USER__));
    } catch(e) {}
    return window.__SERVER_USER__;
  }
  return null;
}
window.getActiveUser = getActiveUser;

/**
 * Menjalankan aturan alur otentikasi:
 * 1. Halaman web mewajibkan login terlebih dahulu (/auth).
 * 2. User biasa dilarang mengakses Dashboard Admin (/admin).
 */
function checkAuthRules() {
  const currentPath = window.location.pathname;
  const user = getActiveUser();

  // Halaman otentikasi (/auth atau /login)
  if (currentPath === '/auth' || currentPath === '/login') {
    if (user) {
      const isAdmin = user.role === 'admin' || (user.email && user.email.includes('admin'));
      if (isAdmin) {
        window.location.href = '/admin';
        return;
      }
      window.location.href = '/';
      return;
    }
    return; // Belum login, tetap di /auth
  }

  // Halaman selain /auth: Wajib login terlebih dahulu
  if (!user) {
    window.location.href = '/auth';
    return;
  }

  const isAdmin = user.role === 'admin' || (user.email && user.email.includes('admin'));

  // Aturan: User biasa dilarang mengakses Dashboard Admin
  if (currentPath.startsWith('/admin')) {
    if (!isAdmin) {
      alert('Akses Ditolak: Anda login sebagai user dan tidak memiliki akses ke Dashboard Admin.');
      window.location.href = '/list';
      return;
    }
  }
}

/**
 * Memeriksa apakah data profil pengguna sudah lengkap (nama, alamat, telepon, kode pos).
 * 
 * @param {Object} [u] Objek user opsional
 * @returns {boolean} True jika profil lengkap
 */
window.isProfileComplete = function(u) {
  const user = u || getActiveUser();
  return Boolean(
    user &&
    user.full_name && String(user.full_name).trim() !== '' &&
    user.address && String(user.address).trim() !== '' &&
    user.phone_number && String(user.phone_number).trim() !== '' &&
    user.postal_code && String(user.postal_code).trim() !== ''
  );
};

// Eksekusi pemeriksaan otentikasi
checkAuthRules();

/**
 * Menyinkronkan info akun user aktif ke header dropdown di semua halaman.
 */
function syncGlobalHeaderUser() {
  const user = getActiveUser();
  if (!user) return;

  const displayName = user.full_name || user.name || user.username || 'User';
  const email = user.email || 'user@wastingmoney.id';
  const isAdmin = user.role === 'admin' || (user.email && user.email.includes('admin'));

  ['dropdownName', 'userDropdownName', 'profileDropdownName', 'adminDropdownName'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = displayName;
  });

  ['dropdownEmail', 'userDropdownEmail', 'profileDropdownEmail', 'adminDropdownEmail'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = email;
  });

  ['authState', 'userAuthState', 'profileAuthState', 'adminAuthState'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
  });

  ['unauthState', 'userUnauthState', 'profileUnauthState', 'adminUnauthState'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
  });

  // Aturan visibilitas berdasarkan role Admin vs User biasa
  if (isAdmin) {
    // Tampilkan link Admin Dashboard
    document.querySelectorAll('a[href="/admin"]').forEach(a => {
      a.style.display = 'block';
    });
    // Sembunyikan keranjang belanja secara menyeluruh karena Admin tidak berbelanja
    document.querySelectorAll('.header-cart-btn, .header-cart-divider, a[title="Keranjang Belanja"], a[href="/cart"]').forEach(el => {
      el.style.display = 'none';
    });
  } else {
    // Sembunyikan link Admin Dashboard bagi user non-admin di menu navigasi
    document.querySelectorAll('a[href="/admin"]').forEach(a => {
      if (!window.location.pathname.startsWith('/admin')) {
        a.style.display = 'none';
      }
    });
  }

  if (window.lucide) lucide.createIcons();
}
window.syncGlobalHeaderUser = syncGlobalHeaderUser;
document.addEventListener('DOMContentLoaded', syncGlobalHeaderUser);

/**
 * Logout universal untuk seluruh halaman aplikasi.
 * Menghapus seluruh token & data di localStorage, sessionStorage,
 * serta menghapus sesi Laravel di server dan mengarahkan ke /auth.
 */
async function handleLogout() {
  // 1. Bersihkan seluruh penyimpanan lokal browser
  localStorage.removeItem('wm_user');
  localStorage.removeItem('wm_token');
  localStorage.removeItem('rakap_token');
  localStorage.removeItem('wm_user_token');
  localStorage.removeItem('wm_user_data');
  localStorage.removeItem('wm_user_profile');
  sessionStorage.clear();
  window.__SERVER_USER__ = null;

  // 2. Beritahu server Laravel untuk menghapus sesi dan redirect ke /logout
  try {
    await fetch('/api/auth/logout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      }
    });
  } catch (e) {
    console.warn('Logout server request failed:', e);
  } finally {
    window.location.href = '/logout';
  }
}
window.handleLogout = handleLogout;
window.handleUserLogout = handleLogout;
window.handleAdminLogout = handleLogout;

/**
 * Menampilkan notifikasi popup (toast notification) di pojok kanan bawah.
 * Otomatis memudar dan menghilang setelah 3 detik.
 * 
 * @param {string} msg Pesan teks notifikasi yang ingin disampaikan ke user
 */
function showToast(msg) {
  const toast = document.getElementById('globalToast');
  const msgEl = document.getElementById('toastMessage');
  if (!toast || !msgEl) return;

  msgEl.textContent = msg;
  toast.classList.remove('hidden');

  setTimeout(() => {
    toast.classList.remove('translate-y-4', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
  }, 20);

  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('translate-y-4', 'opacity-0');
    setTimeout(() => toast.classList.add('hidden'), 300);
  }, 3000);
}
window.showToast = showToast;

/**
 * Membuka atau menutup menu custom dropdown secara global.
 * Otomatis menutup custom dropdown lain yang sedang terbuka di layar.
 * 
 * @param {string} menuId ID elemen menu dropdown
 * @param {string} btnId ID elemen tombol trigger dropdown
 */
function toggleDropdownMenu(menuId, btnId) {
  const menu = document.getElementById(menuId);
  const btn = document.getElementById(btnId);
  if (!menu || !btn) return;
  const isOpen = menu.classList.contains('show');

  // Tutup semua dropdown lain yang sedang aktif
  document.querySelectorAll('.wm-dropdown-menu').forEach(m => {
    if (m !== menu) m.classList.remove('show');
  });
  document.querySelectorAll('.wm-dropdown-trigger').forEach(b => {
    if (b !== btn) b.classList.remove('open');
  });

  if (isOpen) {
    menu.classList.remove('show');
    btn.classList.remove('open');
  } else {
    menu.classList.add('show');
    btn.classList.add('open');
    if (window.lucide) lucide.createIcons();
  }
}
window.toggleDropdownMenu = toggleDropdownMenu;

/**
 * Helper generik saat opsi item dropdown diklik.
 * Mengupdate nilai pada input hidden, teks label trigger, dan memindahkan class 'selected'.
 * 
 * @param {string} hiddenInputId ID input hidden penampung nilai
 * @param {string} triggerTextId ID elemen teks penampil nilai di tombol trigger
 * @param {string} value Nilai terpilih (value)
 * @param {string} displayText Teks tampilan yang ramah pengguna
 * @param {string} menuId ID elemen menu dropdown
 * @param {string} btnId ID elemen tombol trigger
 */
function selectGenericOption(hiddenInputId, triggerTextId, value, displayText, menuId, btnId) {
  const input = document.getElementById(hiddenInputId);
  const textEl = document.getElementById(triggerTextId);
  if (input) {
    input.value = value;
    input.dispatchEvent(new Event('change'));
  }
  if (textEl) textEl.textContent = displayText || value;

  const menu = document.getElementById(menuId);
  if (menu) {
    menu.querySelectorAll('.wm-dropdown-item').forEach(item => {
      if (item.getAttribute('data-value') === value) {
        item.classList.add('selected');
      } else {
        item.classList.remove('selected');
      }
    });
    menu.classList.remove('show');
  }

  const btn = document.getElementById(btnId);
  if (btn) btn.classList.remove('open');
}
window.selectGenericOption = selectGenericOption;

// Event listener: Menutup dropdown jika pengguna mengklik di luar wrapper dropdown
document.addEventListener('click', function(e) {
  if (!e.target.closest('.wm-dropdown-wrapper')) {
    document.querySelectorAll('.wm-dropdown-menu').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('.wm-dropdown-trigger').forEach(b => b.classList.remove('open'));
  }
});

// Event listener: Menutup dropdown atau custom alert jika tombol Escape ditekan
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.wm-dropdown-menu').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('.wm-dropdown-trigger').forEach(b => b.classList.remove('open'));
    if (typeof window.closeCustomAlert === 'function') {
      window.closeCustomAlert();
    }
  }
});

/**
 * =========================================================================
 * Custom Designed Alert & Confirmation Modal Controller
 * =========================================================================
 * Menampilkan alert kustom dengan styling visual premium, menggantikan
 * alert() bawaan browser yang kaku.
 * 
 * @param {Object} options Konfigurasi modal
 * @param {string} [options.title] Judul modal
 * @param {string} [options.message] Pesan modal
 * @param {string} [options.confirmText] Teks tombol utama
 * @param {string|boolean} [options.cancelText] Teks tombol sekunder (false untuk sembunyikan)
 * @param {'warning'|'danger'|'info'} [options.type] Tipe alert (menentukan warna icon badge)
 * @param {string} [options.icon] Nama icon Lucide
 * @param {boolean} [options.showDraftNotice] Menampilkan keterangan draf tersimpan
 * @param {Function} [options.onConfirm] Callback saat tombol utama diklik
 * @param {Function} [options.onCancel] Callback saat tombol batal diklik
 */
let customAlertConfirmCallback = null;
let customAlertCancelCallback = null;

function showCustomAlert(options = {}) {
  const overlay = document.getElementById('wmCustomAlertOverlay');
  const titleEl = document.getElementById('wmAlertTitle');
  const msgEl = document.getElementById('wmAlertMessage');
  const primaryText = document.getElementById('wmAlertPrimaryText');
  const secondaryBtn = document.getElementById('wmAlertSecondaryBtn');
  const draftNotice = document.getElementById('wmAlertDraftNotice');
  const iconWrapper = document.getElementById('wmAlertIconWrapper');
  const icon = document.getElementById('wmAlertIcon');

  if (!overlay) return;

  if (titleEl) titleEl.textContent = options.title || 'Pemberitahuan';
  if (msgEl) msgEl.textContent = options.message || '';
  if (primaryText) primaryText.textContent = options.confirmText || 'OK';

  if (secondaryBtn) {
    if (options.cancelText === false || options.showCancel === false) {
      secondaryBtn.style.display = 'none';
    } else {
      secondaryBtn.style.display = 'inline-flex';
      secondaryBtn.textContent = options.cancelText || 'Tutup';
    }
  }

  if (draftNotice) {
    if (options.showDraftNotice) {
      draftNotice.classList.remove('hidden');
    } else {
      draftNotice.classList.add('hidden');
    }
  }

  if (iconWrapper && icon) {
    const type = options.type || 'warning';
    iconWrapper.className = `wm-alert-icon-wrapper ${type}`;
    const defaultIcon = (type === 'danger') ? 'alert-triangle' : (type === 'info' ? 'info' : 'user-cog');
    icon.setAttribute('data-lucide', options.icon || defaultIcon);
  }

  customAlertConfirmCallback = (typeof options.onConfirm === 'function') ? options.onConfirm : null;
  customAlertCancelCallback = (typeof options.onCancel === 'function') ? options.onCancel : null;

  overlay.classList.add('active');
  document.body.style.overflow = 'hidden';

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.showCustomAlert = showCustomAlert;

function closeCustomAlert() {
  const overlay = document.getElementById('wmCustomAlertOverlay');
  if (overlay) overlay.classList.remove('active');
  document.body.style.overflow = '';
  if (typeof customAlertCancelCallback === 'function') {
    const cb = customAlertCancelCallback;
    customAlertCancelCallback = null;
    cb();
  }
}
window.closeCustomAlert = closeCustomAlert;

function handleCustomAlertBackdrop(e) {
  if (e.target && e.target.id === 'wmCustomAlertOverlay') {
    closeCustomAlert();
  }
}
window.handleCustomAlertBackdrop = handleCustomAlertBackdrop;

function executeCustomAlertConfirm() {
  const cb = customAlertConfirmCallback;
  customAlertConfirmCallback = null;
  closeCustomAlert();
  if (typeof cb === 'function') {
    cb();
  }
}
window.executeCustomAlertConfirm = executeCustomAlertConfirm;

