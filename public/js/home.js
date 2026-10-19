/**
 * wastingmoney - Home Page Controller & Submission Handler
 * File: public/js/home.js
 */

/**
 * Menampilkan atau menyembunyikan menu pop-up dropdown user di header.
 */
function toggleUserDropdown() {
  const d = document.getElementById('userDropdown');
  if (d) d.classList.toggle('hidden');
}
window.toggleUserDropdown = toggleUserDropdown;

// Menutup dropdown jika pengguna mengklik di luar area menu
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('userMenuWrapper');
  const d = document.getElementById('userDropdown');
  if (wrap && d && !wrap.contains(e.target)) {
    d.classList.add('hidden');
  }
});

/**
 * Event listener saat user memilih file gambar merch melalui dialog file.
 * Menampilkan nama file yang dipilih pada label badge.
 * 
 * @param {HTMLInputElement} input Elemen input file
 */
function handleFileSelected(input) {
  if (input.files && input.files[0]) {
    document.getElementById('fileNameLabel').textContent = input.files[0].name;
  }
}
window.handleFileSelected = handleFileSelected;

/**
 * Memulihkan draft data formulir request jika sebelumnya user sempat mengisinya
 * sebelum diarahkan untuk melengkapi profil data diri.
 */
function restoreHomeDraft() {
  const draftStr = localStorage.getItem('wm_home_draft');
  if (!draftStr) return;

  try {
    const draft = JSON.parse(draftStr);
    let restored = false;

    if (draft.artistBand) {
      const el = document.getElementById('artistBand');
      if (el) el.value = draft.artistBand;
      restored = true;
    }
    if (draft.merchType) {
      const el = document.getElementById('merchType');
      if (el) el.value = draft.merchType;
      const triggerText = document.getElementById('merchTypeTriggerText');
      if (triggerText) triggerText.textContent = draft.merchType;
      document.querySelectorAll('#merchTypeMenu .home-dropdown-item').forEach(item => {
        item.classList.toggle('selected', item.getAttribute('data-value') === draft.merchType);
      });
      restored = true;
    }
    if (draft.releasedYear) {
      const el = document.getElementById('releasedYear');
      if (el) el.value = draft.releasedYear;
      const yrTrigger = document.getElementById('releasedYearTriggerText');
      if (yrTrigger) yrTrigger.textContent = draft.releasedYear;
      document.querySelectorAll('#releasedYearMenu .home-dropdown-item').forEach(item => {
        item.classList.toggle('selected', item.getAttribute('data-value') === draft.releasedYear);
      });
      restored = true;
    }
    if (draft.merchEdition) {
      const el = document.getElementById('merchEdition');
      if (el) el.value = draft.merchEdition;
      restored = true;
    }
    if (draft.description) {
      const el = document.getElementById('moreDescription');
      if (el) el.value = draft.description;
      restored = true;
    }
    if (draft.fileName && draft.fileName !== 'img-01.jpg') {
      const el = document.getElementById('fileNameLabel');
      if (el) el.textContent = draft.fileName;
    }

    if (restored && typeof showToast === 'function') {
      showToast('Data request Anda telah dimuat kembali. Silakan klik Submit.');
    }
  } catch (e) {
    console.warn('Gagal memulihkan draft request:', e);
  }
}

document.addEventListener('DOMContentLoaded', restoreHomeDraft);
restoreHomeDraft();

/**
 * Event listener submit formulir pencarian/request merch baru.
 * Mengirim payload ke backend API (POST /api/submissions),
 * menyimpan ke database SQLite & Firebase Firestore, serta mereset form jika berhasil.
 */
document.addEventListener('DOMContentLoaded', function() {
  const merchForm = document.getElementById('merchForm');
  if (!merchForm) return;

  merchForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const successEl = document.getElementById('formSuccessAlert');
    const errorEl = document.getElementById('formErrorAlert');
    const errorMsg = document.getElementById('formErrorMessage');
    const btn = document.getElementById('submitButton');
    const label = document.getElementById('submitLabel');
    const loading = document.getElementById('submitLoading');

    if (successEl) successEl.classList.add('hidden');
    if (errorEl) errorEl.classList.add('hidden');

    // 1. Cek status otentikasi login
    const user = typeof getActiveUser === 'function' ? getActiveUser() : null;
    if (!user) {
      if (typeof window.showCustomAlert === 'function') {
        window.showCustomAlert({
          title: 'Masuk Akun',
          message: 'Silakan login terlebih dahulu untuk mengajukan request belanja.',
          confirmText: 'Masuk Sekarang',
          cancelText: 'Tutup',
          type: 'info',
          icon: 'log-in',
          onConfirm: () => {
            window.location.href = '/auth';
          }
        });
      } else {
        window.location.href = '/auth';
      }
      return;
    }

    // 2. Cek apakah data profil pengguna sudah lengkap
    const isComplete = typeof window.isProfileComplete === 'function' 
      ? window.isProfileComplete(user)
      : Boolean(user.full_name && user.address && user.phone_number && user.postal_code);

    if (!isComplete) {
      // Simpan data formulir yang sudah sempat diisi oleh user ke draft localStorage
      const currentArtist = document.getElementById('artistBand') ? document.getElementById('artistBand').value.trim() : '';
      const currentEdition = document.getElementById('merchEdition') ? document.getElementById('merchEdition').value.trim() : '';
      const currentDesc = document.getElementById('moreDescription') ? document.getElementById('moreDescription').value.trim() : '';
      const currentMerchType = document.getElementById('merchType') ? document.getElementById('merchType').value : 'T-Shirt';
      const currentYear = document.getElementById('releasedYear') ? document.getElementById('releasedYear').value : '2026';
      const currentFileName = document.getElementById('fileNameLabel') ? document.getElementById('fileNameLabel').textContent.trim() : 'img-01.jpg';

      const hasFilledData = Boolean(currentArtist || currentEdition || currentDesc);
      if (hasFilledData) {
        const draft = {
          artistBand: currentArtist,
          merchType: currentMerchType,
          releasedYear: currentYear,
          merchEdition: currentEdition,
          description: currentDesc,
          fileName: currentFileName
        };
        localStorage.setItem('wm_home_draft', JSON.stringify(draft));
      } else {
        localStorage.removeItem('wm_home_draft');
      }

      // Tampilkan Custom Designed Modal Alert (menggantikan browser alert default)
      if (typeof window.showCustomAlert === 'function') {
        window.showCustomAlert({
          title: 'Lengkapi Data Diri',
          message: 'Anda harus melengkapi data diri Anda terlebih dahulu di Edit Profile sebelum dapat mengajukan request.',
          confirmText: 'Edit Profile Sekarang',
          cancelText: 'Nanti Saja',
          showDraftNotice: hasFilledData,
          type: 'warning',
          icon: 'user-cog',
          onConfirm: () => {
            window.location.href = '/profile?incomplete=1&return_to=home';
          }
        });
      } else {
        window.location.href = '/profile?incomplete=1&return_to=home';
      }
      return;
    }

    // 3. Validasi field wajib
    const artistBand = document.getElementById('artistBand').value.trim();
    if (!artistBand) {
      if (errorMsg) errorMsg.textContent = 'Field Artist/band wajib diisi.';
      if (errorEl) errorEl.classList.remove('hidden');
      return;
    }

    if (btn) btn.disabled = true;
    if (label) label.textContent = 'Submitting...';
    if (loading) loading.classList.remove('hidden');

    const payload = {
      artistBand: artistBand,
      merchType: document.getElementById('merchType').value,
      releasedYear: document.getElementById('releasedYear').value,
      merchEdition: document.getElementById('merchEdition').value.trim(),
      description: document.getElementById('moreDescription').value.trim(),
      additionalData: {
        fileName: document.getElementById('fileNameLabel').textContent.trim()
      }
    };

    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    try {
      const response = await fetch('/api/submissions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: JSON.stringify(payload)
      });

      const res = await response.json();

      if (res.success) {
        if (successEl) successEl.classList.remove('hidden');
        localStorage.removeItem('wm_home_draft'); // Hapus draft karena formulir sukses terkirim

        // Simpan request ke antrean keranjang lokal pengguna dengan status awal Queue (Abu-abu)
        try {
          const userRequests = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');
          const serverData = res.data || {};
          const newCartItem = {
            id: serverData.id || Date.now(),
            requestId: serverData.request_id ? (String(serverData.request_id).startsWith('#') ? serverData.request_id : '#' + serverData.request_id) : '#' + String(Date.now()).slice(-6),
            artistBand: serverData.artist_band || serverData.title || payload.artistBand,
            merchType: serverData.merch_type || payload.merchType,
            releasedYear: serverData.released_year || payload.releasedYear,
            merchEdition: serverData.merch_edition || payload.merchEdition || '-',
            description: serverData.description || payload.description || '-',
            price: '-',
            sellerNotes: '-',
            status: 'Queue', // Awal mula Abu-abu di Keranjang
            statusDate: new Date().toLocaleDateString('id-ID') + ' ' + new Date().toLocaleTimeString('id-ID'),
            theme: 'theme-gray',
            dotClass: 'dot-gray',
            image: serverData.image_url || '/assets/taylor_swift_shirt.png',
            fullName: user.full_name || user.name || 'User',
            address: user.address || '-',
            postalCode: user.postal_code || '-',
            phoneNumber: user.phone_number || '-'
          };
          userRequests.unshift(newCartItem);
          localStorage.setItem('wm_user_created_requests', JSON.stringify(userRequests));
        } catch(e) {
          console.error('Error saving local cart item:', e);
        }

        document.getElementById('artistBand').value = '';
        document.getElementById('merchEdition').value = '';
        document.getElementById('moreDescription').value = '';
        document.getElementById('fileNameLabel').textContent = 'img-01.jpg';
        selectGenericOption('merchType', 'merchTypeTriggerText', 'T-Shirt', 'T-Shirt', 'merchTypeMenu', 'merchTypeBtn');
        selectGenericOption('releasedYear', 'releasedYearTriggerText', '2026', '2026', 'releasedYearMenu', 'releasedYearBtn');
      } else {
        if (errorMsg) errorMsg.textContent = res.message || 'Gagal mengirim permintaan. Silakan coba lagi.';
        if (errorEl) errorEl.classList.remove('hidden');
      }
    } catch(err) {
      if (errorMsg) errorMsg.textContent = 'Gagal mengirim permintaan. Periksa koneksi jaringan.';
      if (errorEl) errorEl.classList.remove('hidden');
    } finally {
      if (btn) btn.disabled = false;
      if (label) label.textContent = 'Submit';
      if (loading) loading.classList.add('hidden');
      if (window.lucide) lucide.createIcons();
    }
  });
});
