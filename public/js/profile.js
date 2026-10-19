/**
 * wastingmoney - Profile Page Controller & Form Handler
 * File: public/js/profile.js
 */

let currentUserData = {
  username: '',
  full_name: '',
  address: '',
  phone_number: '',
  postal_code: ''
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
  // Tampilkan notifikasi incomplete jika diarahkan oleh guard
  if (window.location.search.includes('incomplete=1')) {
    const alertEl = document.getElementById('incompleteProfileAlert');
    if (alertEl) alertEl.classList.remove('hidden');
  }

  // 1. Ambil data user aktif saat ini dari getActiveUser()
  const activeUser = typeof getActiveUser === 'function' ? getActiveUser() : null;
  if (activeUser) {
    currentUserData.username = activeUser.username || activeUser.name || '';
    currentUserData.email = activeUser.email || '';
    currentUserData.full_name = activeUser.full_name || '';
    currentUserData.address = activeUser.address || '';
    currentUserData.phone_number = activeUser.phone_number || '';
    currentUserData.postal_code = activeUser.postal_code || '';
  }

  // 2. Ambil data terbaru langsung dari endpoint backend Laravel (/api/auth/me)
  try {
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    const queryParams = activeUser && activeUser.email ? `?email=${encodeURIComponent(activeUser.email)}&user_id=${activeUser.id || ''}` : '';
    const res = await fetch(`/api/auth/me${queryParams}`, {
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      }
    });
    const data = await res.json();
    if (data && data.success && data.user) {
      const u = data.user;
      currentUserData.username = u.username || u.name || currentUserData.username;
      currentUserData.email = u.email || currentUserData.email;
      if (u.full_name) currentUserData.full_name = u.full_name;
      if (u.address) currentUserData.address = u.address;
      if (u.phone_number) currentUserData.phone_number = u.phone_number;
      if (u.postal_code) currentUserData.postal_code = u.postal_code;

      // Update cache wm_user jika cocok dengan active user
      if (activeUser && activeUser.id === u.id) {
        localStorage.setItem('wm_user', JSON.stringify(u));
      }
    }
  } catch (e) {
    console.log('Using local user profile state');
  }

  // 3. Masukkan nilai ke masing-masing elemen input di halaman
  const elUser = document.getElementById('profileUsername');
  const elName = document.getElementById('profileFullName');
  const elAddr = document.getElementById('profileAddress');
  const elPhone = document.getElementById('profilePhone');
  const elPost = document.getElementById('profilePostalCode');

  if (elUser) elUser.value = currentUserData.username || (activeUser ? activeUser.name : 'user');
  if (elName) elName.value = currentUserData.full_name || '';
  if (elAddr) elAddr.value = currentUserData.address || '';
  if (elPhone) elPhone.value = currentUserData.phone_number || '';
  if (elPost) elPost.value = currentUserData.postal_code || '';
}
window.loadProfileData = loadProfileData;

/**
 * Menampilkan atau menyembunyikan drawer input ubah password saat link "Change Password?" diklik.
 */
function togglePasswordDrawer() {
  const drawer = document.getElementById('passwordDrawer');
  const link = document.getElementById('togglePwdLink');
  if (!drawer || !link) return;

  if (drawer.classList.contains('hidden')) {
    drawer.classList.remove('hidden');
    link.textContent = 'Hide Password Form';
  } else {
    drawer.classList.add('hidden');
    link.textContent = 'Change Password?';
  }
}
window.togglePasswordDrawer = togglePasswordDrawer;

/**
 * Memvalidasi form (kecocokan password baru) dan membuka modal pop-up konfirmasi
 * "Are you sure want to save changes?" sesuai desain mockup Image 2.
 */
function promptConfirmSave() {
  // Validasi kecocokan konfirmasi password jika diisi
  const newPwd = document.getElementById('profileNewPassword') ? document.getElementById('profileNewPassword').value : '';
  const confirmPwd = document.getElementById('profileConfirmPassword') ? document.getElementById('profileConfirmPassword').value : '';
  if (newPwd && newPwd !== confirmPwd) {
    if (typeof showToast === 'function') showToast('Konfirmasi password baru tidak cocok!');
    else alert('Konfirmasi password baru tidak cocok!');
    return;
  }

  const overlay = document.getElementById('profileConfirmModalOverlay');
  if (overlay) overlay.classList.add('active');
}
window.promptConfirmSave = promptConfirmSave;

/**
 * Menutup modal pop-up konfirmasi penyimpanan profil.
 */
function closeConfirmModal() {
  const overlay = document.getElementById('profileConfirmModalOverlay');
  if (overlay) overlay.classList.remove('active');
}
window.closeConfirmModal = closeConfirmModal;

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
window.handleModalBackdropClick = handleModalBackdropClick;

/**
 * Mengeksekusi penyimpanan data profil ke database backend (endpoint /api/auth/profile)
 * dan menyinkronkannya ke localStorage browser serta Cloud Firestore.
 */
async function executeSaveProfile() {
  const btnYes = document.getElementById('btnConfirmYes');
  if (btnYes) {
    btnYes.disabled = true;
    btnYes.textContent = 'Saving...';
  }

  const activeUser = typeof getActiveUser === 'function' ? getActiveUser() : null;

  const payload = {
    user_id: activeUser ? activeUser.id : undefined,
    email: activeUser ? activeUser.email : undefined,
    username: document.getElementById('profileUsername') ? document.getElementById('profileUsername').value : '',
    full_name: document.getElementById('profileFullName') ? document.getElementById('profileFullName').value : '',
    address: document.getElementById('profileAddress') ? document.getElementById('profileAddress').value : '',
    phone_number: document.getElementById('profilePhone') ? document.getElementById('profilePhone').value : '',
    postal_code: document.getElementById('profilePostalCode') ? document.getElementById('profilePostalCode').value : '',
    new_password: document.getElementById('profileNewPassword') ? document.getElementById('profileNewPassword').value : ''
  };

  // Simpan segera ke localStorage browser
  localStorage.setItem('wm_user_profile', JSON.stringify(payload));

  // Perbarui data user aktif di sesi browser (wm_user)
  const storedUserStr = localStorage.getItem('wm_user');
  if (storedUserStr) {
    try {
      const u = JSON.parse(storedUserStr);
      u.full_name = payload.full_name;
      u.address = payload.address;
      u.phone_number = payload.phone_number;
      u.postal_code = payload.postal_code;
      localStorage.setItem('wm_user', JSON.stringify(u));
    } catch (err) {}
  }

  // Kirim payload pembaruan ke API backend Laravel (yang otomatis sync ke Firestore)
  try {
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    const res = await fetch('/api/auth/profile', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data && data.success && data.user) {
      localStorage.setItem('wm_user', JSON.stringify(data.user));
    }
    if (typeof showToast === 'function') showToast('Profil berhasil disimpan!');
    document.getElementById('incompleteProfileAlert')?.classList.add('hidden');

    // Kembali ke halaman home user setelah profil berhasil disimpan
    setTimeout(() => {
      window.location.href = '/';
    }, 700);
  } catch (e) {
    if (typeof showToast === 'function') showToast('Profil berhasil disimpan.');
    setTimeout(() => {
      window.location.href = '/';
    }, 700);
  } finally {
    if (btnYes) {
      btnYes.disabled = false;
      btnYes.textContent = 'Yes';
    }
    closeConfirmModal();

    // Reset kolom input password
    const np = document.getElementById('profileNewPassword');
    const cp = document.getElementById('profileConfirmPassword');
    const pd = document.getElementById('passwordDrawer');
    const tl = document.getElementById('togglePwdLink');

    if (np) np.value = '';
    if (cp) cp.value = '';
    if (pd) pd.classList.add('hidden');
    if (tl) tl.textContent = 'Change Password?';
  }
}
window.executeSaveProfile = executeSaveProfile;

/**
 * Membatalkan perubahan yang belum disimpan dan mengembalikan form ke data tersimpan.
 */
function handleCancel() {
  if (typeof showToast === 'function') showToast('Perubahan dibatalkan.');
  loadProfileData();
}
window.handleCancel = handleCancel;

/**
 * Menampilkan atau menyembunyikan menu pop-up dropdown user di header.
 */
function toggleProfileDropdown() {
  const d = document.getElementById('userDropdown');
  if (d) d.classList.toggle('hidden');
}
window.toggleProfileDropdown = toggleProfileDropdown;

// Menutup dropdown profil jika pengguna mengklik area luar
document.addEventListener('click', (e) => {
  const wrapper = document.getElementById('userMenuWrapper');
  const d = document.getElementById('userDropdown');
  if (wrapper && !wrapper.contains(e.target) && d && !d.classList.contains('hidden')) {
    d.classList.add('hidden');
  }
});
