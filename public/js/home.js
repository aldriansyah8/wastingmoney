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

let selectedMerchImageDataUrl = null;

/**
 * Event listener saat user memilih file gambar merch melalui dialog file.
 * Menampilkan nama file pada badge, menampilkan thumbnail langsung di upload box dan preview bar,
 * serta menyiapkan data URL untuk preview resolusi tinggi di modal preview.
 * 
 * @param {HTMLInputElement} input Elemen input file
 */
function handleFileSelected(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const fileName = file.name;

    const lbl = document.getElementById('fileNameLabel');
    if (lbl) lbl.textContent = fileName;

    const previewName = document.getElementById('formImagePreviewName');
    if (previewName) previewName.textContent = fileName;

    const previewSub = document.getElementById('homeImagePreviewSubtitle');
    if (previewSub) previewSub.textContent = fileName;

    const reader = new FileReader();
    reader.onload = function(e) {
      selectedMerchImageDataUrl = e.target.result;

      // 1. Tampilkan thumbnail langsung di dalam upload box
      const boxThumb = document.getElementById('uploadBoxThumbImg');
      const defIcon = document.getElementById('uploadDefaultIcon');
      if (boxThumb) {
        boxThumb.src = selectedMerchImageDataUrl;
        boxThumb.classList.remove('hidden');
      }
      if (defIcon) defIcon.classList.add('hidden');

      // 2. Tampilkan bar preview foto hasil upload di form submit
      const previewBar = document.getElementById('formImagePreviewBar');
      const previewThumb = document.getElementById('formImagePreviewThumb');
      const previewFull = document.getElementById('homeImagePreviewFull');

      if (previewThumb) previewThumb.src = selectedMerchImageDataUrl;
      if (previewFull) previewFull.src = selectedMerchImageDataUrl;
      if (previewBar) previewBar.classList.remove('hidden');

      if (window.lucide) lucide.createIcons();
    };
    reader.readAsDataURL(file);
  }
}
window.handleFileSelected = handleFileSelected;

/**
 * Membuka modal preview foto merchandise sebelum disubmit.
 */
function openHomeImagePreviewModal() {
  const modal = document.getElementById('homeImagePreviewModal');
  if (modal && selectedMerchImageDataUrl) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
  }
}
window.openHomeImagePreviewModal = openHomeImagePreviewModal;

/**
 * Menutup modal preview foto merchandise.
 */
function closeHomeImagePreviewModal() {
  const modal = document.getElementById('homeImagePreviewModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  }
}
window.closeHomeImagePreviewModal = closeHomeImagePreviewModal;

/**
 * Menangani klik pada area backdrop luar modal preview foto.
 * 
 * @param {MouseEvent} event Event klik mouse
 */
function handleHomePreviewBackdrop(event) {
  if (event.target === document.getElementById('homeImagePreviewModal')) {
    closeHomeImagePreviewModal();
  }
}
window.handleHomePreviewBackdrop = handleHomePreviewBackdrop;

/**
 * Menghapus/mereset foto yang telah dipilih oleh pengguna.
 */
function clearSelectedImage() {
  selectedMerchImageDataUrl = null;
  const input = document.getElementById('merchImageInput');
  if (input) input.value = '';

  const boxThumb = document.getElementById('uploadBoxThumbImg');
  const defIcon = document.getElementById('uploadDefaultIcon');
  if (boxThumb) {
    boxThumb.src = '';
    boxThumb.classList.add('hidden');
  }
  if (defIcon) defIcon.classList.remove('hidden');

  const previewBar = document.getElementById('formImagePreviewBar');
  if (previewBar) previewBar.classList.add('hidden');

  const lbl = document.getElementById('fileNameLabel');
  if (lbl) lbl.textContent = 'img-01.jpg';

  closeHomeImagePreviewModal();
}
window.clearSelectedImage = clearSelectedImage;

// Listener tombol Escape untuk menutup preview modal
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeHomeImagePreviewModal();
  }
});

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
      const currentYear = document.getElementById('releasedYear') ? document.getElementById('releasedYear').value : String(new Date().getFullYear());
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

    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    const fileInput = document.getElementById('merchImageInput');
    const hasPhysicalFile = fileInput && fileInput.files && fileInput.files[0];

    let fetchOptions;
    if (hasPhysicalFile) {
      const formData = new FormData();
      formData.append('artistBand', artistBand);
      formData.append('merchType', document.getElementById('merchType').value);
      formData.append('releasedYear', document.getElementById('releasedYear').value);
      formData.append('merchEdition', document.getElementById('merchEdition').value.trim());
      formData.append('description', document.getElementById('moreDescription').value.trim());
      formData.append('user_id', user.id || '');
      formData.append('user_email', user.email || '');
      formData.append('user_name', user.name || user.username || '');
      formData.append('image', fileInput.files[0]);
      formData.append('additionalData[fileName]', fileInput.files[0].name);

      fetchOptions = {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: formData
      };
    } else {
      const payload = {
        artistBand: artistBand,
        merchType: document.getElementById('merchType').value,
        releasedYear: document.getElementById('releasedYear').value,
        merchEdition: document.getElementById('merchEdition').value.trim(),
        description: document.getElementById('moreDescription').value.trim(),
        user_id: user.id || '',
        user_email: user.email || '',
        user_name: user.name || user.username || '',
        image_data_url: selectedMerchImageDataUrl || '',
        additionalData: {
          fileName: document.getElementById('fileNameLabel').textContent.trim()
        }
      };

      fetchOptions = {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: JSON.stringify(payload)
      };
    }

    try {
      const response = await fetch('/api/submissions', fetchOptions);
      const res = await response.json();

      if (res.success) {
        if (successEl) successEl.classList.remove('hidden');
        localStorage.removeItem('wm_home_draft'); // Hapus draft karena formulir sukses terkirim

        // Simpan request ke antrean Merch Request List lokal pengguna dengan status awal Queue (Abu-abu)
        try {
          const userRequests = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');
          const serverData = res.data || {};
          const finalImage = serverData.image_url || selectedMerchImageDataUrl || '/assets/placeholder_merch.svg';
          
          const now = new Date();
          const day = String(now.getDate()).padStart(2, '0');
          const month = String(now.getMonth() + 1).padStart(2, '0');
          const year = now.getFullYear();
          const hours = String(now.getHours()).padStart(2, '0');
          const minutes = String(now.getMinutes()).padStart(2, '0');
          const seconds = String(now.getSeconds()).padStart(2, '0');
          const formattedNow = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;

          const newCartItem = {
            id: serverData.id || Date.now(),
            user_id: user.id || null,
            user_email: user.email || null,
            user_name: user.name || user.username || null,
            requestId: serverData.request_id ? (String(serverData.request_id).startsWith('#') ? serverData.request_id : '#' + serverData.request_id) : '#' + String(Date.now()).slice(-6),
            artistBand: serverData.artist_band || serverData.title || artistBand,
            merchType: serverData.merch_type || document.getElementById('merchType').value,
            releasedYear: serverData.released_year || document.getElementById('releasedYear').value,
            merchEdition: serverData.merch_edition || document.getElementById('merchEdition').value.trim() || '-',
            description: serverData.description || document.getElementById('moreDescription').value.trim() || '-',
            price: '-',
            sellerNotes: '-',
            status: 'Queue', // Awal mula Abu-abu di Merch Request List
            statusDate: formattedNow,
            created_at: now.toISOString(),
            createdAt: now.toISOString(),
            theme: 'theme-gray',
            dotClass: 'dot-gray',
            image: finalImage,
            fullName: user.full_name || user.name || 'User',
            address: user.address || '-',
            postalCode: user.postal_code || '-',
            phoneNumber: user.phone_number || '-'
          };
          userRequests.unshift(newCartItem);
          localStorage.setItem('wm_user_created_requests', JSON.stringify(userRequests));
          selectedMerchImageDataUrl = null;
        } catch(e) {
          console.error('Error saving local cart item:', e);
        }

        document.getElementById('artistBand').value = '';
        document.getElementById('merchEdition').value = '';
        document.getElementById('moreDescription').value = '';
        clearSelectedImage();
        selectGenericOption('merchType', 'merchTypeTriggerText', 'T-Shirt', 'T-Shirt', 'merchTypeMenu', 'merchTypeBtn');
        const currentYearStr = String(new Date().getFullYear());
        selectGenericOption('releasedYear', 'releasedYearTriggerText', currentYearStr, currentYearStr, 'releasedYearMenu', 'releasedYearBtn');
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
