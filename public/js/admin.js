/**
 * wastingmoney - Admin Dashboard Script
 * File: public/js/admin.js
 * 
 * Modul JavaScript terpisah khusus untuk halaman Admin Dashboard (/admin).
 * Mengatur pengambilan data seluruh request pengajuan (/api/admin/submissions),
 * rendering tabel pengajuan, modal review barang & kalkulasi mata uang/harga,
 * pop-up modal konfirmasi makesure ("Are you sure want to save changes?"),
 * serta pengiriman update status ke backend (/api/admin/submissions/{id}/status).
 */

// Inisialisasi ikon Lucide
if (window.lucide) {
  lucide.createIcons();
}

let adminSubmissionsList = [];
let selectedId = null;
let selectedRequestId = null;

const currencySymbols = {
  'IDR': 'Rp',
  'USD': '$',
  'EUR': '€',
  'GBP': '£',
  'JPY': '¥',
  'SGD': 'S$',
  'AUD': 'A$',
  'MYR': 'RM'
};

const statusDotClasses = {
  'Queue': 'wm-status-dot-queue',
  'Process': 'wm-status-dot-process',
  'In Progress': 'wm-status-dot-process',
  'Found!': 'wm-status-dot-found',
  'Approved': 'wm-status-dot-found',
  'Paid': 'wm-status-dot-paid',
  'Cancel': 'wm-status-dot-cancel',
  'Rejected': 'wm-status-dot-rejected',
  'Pending': 'wm-status-dot-queue'
};

/**
 * Mengubah pilihan status request pada custom dropdown modal review admin.
 * Mengupdate nilai input hidden, teks trigger, dan indikator warna status dot.
 * 
 * @param {string} status Nama status ('In Progress', 'Approved', 'Rejected', 'Pending')
 */
/**
 * Mengunci atau membuka kontrol modal review admin saat status pesanan adalah 'Paid'.
 * Sesuai instruksi: jika statusnya sudah diubah menjadi Paid oleh admin,
 * maka semua kontrol/tombol yang ada di modal (Status dropdown, Currency dropdown, Price input,
 * Seller notes, tombol upload foto, tombol hapus foto, serta tombol Save Changes) menjadi disabled.
/**
 * Mengunci atau membuka kontrol input dan tombol Save pada modal review admin.
 * Ketika status sudah 'Paid' (Lunas) atau 'Cancel' (Dibatalkan), admin tidak dapat mengubah status, harga, catatan,
 * atau menyimpan perubahan kembali.
 * 
 * @param {boolean} isLocked True jika status 'Paid' atau 'Cancel'
 * @param {string} lockReason 'cancel' atau 'paid'
 */
function updateAdminModalLockState(isLocked, lockReason = 'paid') {
  const statusBtn = document.getElementById('statusDropdownBtn');
  const currBtn = document.getElementById('currencyDropdownBtn');
  const priceInput = document.getElementById('modalPriceAmount');
  const notesInput = document.getElementById('modalNotes');
  const saveBtn = document.getElementById('btnSaveModal');

  if (isLocked) {
    const isCancel = (lockReason === 'cancel');
    const lockMsg = isCancel 
      ? 'Pesanan ini sudah dibatalkan (Cancel) dan terkunci sehingga tidak dapat diedit kembali.'
      : 'Pesanan sudah berstatus Paid (Lunas) dan terkunci.';

    if (statusBtn) {
      statusBtn.disabled = true;
      statusBtn.classList.add('opacity-60', 'cursor-not-allowed');
      statusBtn.style.pointerEvents = 'none';
      statusBtn.title = lockMsg;
    }
    if (currBtn) {
      currBtn.disabled = true;
      currBtn.classList.add('opacity-60', 'cursor-not-allowed');
      currBtn.style.pointerEvents = 'none';
    }
    if (priceInput) {
      priceInput.disabled = true;
      priceInput.readOnly = true;
      priceInput.classList.add('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
    }
    if (notesInput) {
      notesInput.disabled = true;
      notesInput.readOnly = true;
      notesInput.classList.add('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
    }
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
      saveBtn.style.pointerEvents = 'none';
      saveBtn.title = lockMsg;
    }
  } else {
    if (statusBtn) {
      statusBtn.disabled = false;
      statusBtn.classList.remove('opacity-60', 'cursor-not-allowed');
      statusBtn.style.pointerEvents = '';
      statusBtn.title = '';
    }
    if (currBtn) {
      currBtn.disabled = false;
      currBtn.classList.remove('opacity-60', 'cursor-not-allowed');
      currBtn.style.pointerEvents = '';
    }
    if (priceInput) {
      priceInput.disabled = false;
      priceInput.readOnly = false;
      priceInput.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
    }
    if (notesInput) {
      notesInput.disabled = false;
      notesInput.readOnly = false;
      notesInput.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
    }
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
      saveBtn.style.pointerEvents = '';
      saveBtn.title = '';
    }
  }
}
window.updateAdminModalLockState = updateAdminModalLockState;

function selectStatusOption(status) {
  const input = document.getElementById('modalStatus');
  const textEl = document.getElementById('statusTriggerText');
  const dotEl = document.getElementById('statusTriggerDot');
  
  if (input) input.value = status;
  if (textEl) textEl.textContent = status;
  if (dotEl) {
    dotEl.className = 'wm-status-dot ' + (statusDotClasses[status] || 'wm-status-dot-process');
  }

  // Sinkronkan status dan warna dot di bagian atas header modal review
  const topStatusText = document.getElementById('modalTopStatusText');
  const topStatusDot = document.getElementById('modalTopStatusDot');
  if (topStatusText) topStatusText.textContent = status;
  if (topStatusDot) {
    topStatusDot.className = 'wm-status-dot ' + (statusDotClasses[status] || 'wm-status-dot-process');
  }

  const menu = document.getElementById('statusDropdownMenu');
  if (menu) {
    menu.querySelectorAll('.wm-dropdown-item').forEach(item => {
      if (item.getAttribute('data-value') === status) {
        item.classList.add('selected');
      } else {
        item.classList.remove('selected');
      }
    });
    menu.classList.remove('show');
  }

  const btn = document.getElementById('statusDropdownBtn');
  if (btn) btn.classList.remove('open');

  // Perbarui tampilan thumbnail galeri dan status tombol upload secara realtime sesuai aturan status
  if (typeof renderAdminModalPhotos === 'function') {
    renderAdminModalPhotos();
  }
  if (typeof renderAdminModalThumbs === 'function') {
    renderAdminModalThumbs();
  }
}
window.selectStatusOption = selectStatusOption;

/**
 * Mengubah pilihan mata uang pada dropdown harga admin (IDR, USD, EUR, GBP, JPY, dll.).
 * Memperbarui simbol prefix di sebelah kiri input nominal (Rp, $, €, £, ¥, dll.).
 * 
 * @param {string} curr Kode mata uang ISO 3 huruf (contoh: 'IDR', 'USD')
 * @param {string} symbol Simbol mata uang (contoh: 'Rp', '$')
 * @param {string} [label] Label deskriptif opsional
 */
function selectCurrencyOption(curr, symbol, label) {
  const input = document.getElementById('modalCurrency');
  const textEl = document.getElementById('currencyTriggerText');
  const prefixEl = document.getElementById('currencySymbolPrefix');

  if (input) input.value = curr;
  if (textEl) textEl.textContent = curr;
  if (prefixEl) prefixEl.textContent = symbol;

  const menu = document.getElementById('currencyDropdownMenu');
  if (menu) {
    menu.querySelectorAll('.wm-dropdown-item').forEach(item => {
      if (item.getAttribute('data-value') === curr) {
        item.classList.add('selected');
      } else {
        item.classList.remove('selected');
      }
    });
    menu.classList.remove('show');
  }

  const btn = document.getElementById('currencyDropdownBtn');
  if (btn) btn.classList.remove('open');

  updateModalPriceValue();
}
window.selectCurrencyOption = selectCurrencyOption;

/**
 * Memformat input angka nominal harga dengan pemisah ribuan titik (contoh: 800000 -> 800.000).
 * 
 * @param {HTMLInputElement} input Elemen input teks nominal
 */
function formatPriceNominal(input) {
  let raw = input.value.replace(/[^0-9]/g, '');
  if (raw) {
    input.value = new Intl.NumberFormat('id-ID').format(raw);
  } else {
    input.value = '';
  }
  updateModalPriceValue();
}
window.formatPriceNominal = formatPriceNominal;

/**
 * Menggabungkan kode mata uang dan nominal harga menjadi string harga terpadu (contoh: "Rp 800.000").
 */
function updateModalPriceValue() {
  const currEl = document.getElementById('modalCurrency');
  const curr = currEl ? currEl.value : 'IDR';
  const amountEl = document.getElementById('modalPriceAmount');
  const amount = amountEl ? amountEl.value.trim() : '';
  const symbol = currencySymbols[curr] || curr;
  const hiddenPrice = document.getElementById('modalPrice');
  if (!hiddenPrice) return;

  if (!amount || amount === '0' || amount === '-') {
    hiddenPrice.value = '-';
  } else {
    hiddenPrice.value = `${symbol} ${amount}`;
  }
}
window.updateModalPriceValue = updateModalPriceValue;

/**
 * Menampilkan atau menyembunyikan menu pop-up dropdown akun admin di header.
 */
function toggleAdminDropdown() {
  const d = document.getElementById('adminDropdown');
  if (d) d.classList.toggle('hidden');
}
window.toggleAdminDropdown = toggleAdminDropdown;

// Menutup dropdown akun admin saat pengguna mengklik di luar area menu
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('adminMenuWrapper');
  const d = document.getElementById('adminDropdown');
  if (wrap && d && !wrap.contains(e.target)) {
    d.classList.add('hidden');
  }
});

/**
 * Mengubah string format timestamp tanggal menjadi format DD/MM/YYYY.
 * 
 * @param {string} dateStr String tanggal ISO / datetime
 * @return {string} Tanggal terformat (DD/MM/YYYY)
 */
function formatDate(dateStr) {
  if (!dateStr) return '10/10/2026';
  const d = new Date(dateStr);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
}
window.formatDate = formatDate;

/**
 * Menampilkan baris skeleton loading shimmer pada tabel data formulir admin.
 */
function showAdminTableSkeleton(rows = 6) {
  const tbody = document.getElementById('adminTableBody');
  if (!tbody) return;
  let html = '';
  for (let i = 0; i < rows; i++) {
    html += `
      <tr class="admin-skeleton-row">
        <td class="py-4 px-3"><div class="admin-skeleton-cell h-4 w-4"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-16"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-24"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-36"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-16"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-12"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-6 w-20 rounded-full"></div></td>
        <td class="py-4 px-4"><div class="admin-skeleton-cell h-4 w-20"></div></td>
        <td class="py-4 px-4 text-center"><div class="admin-skeleton-cell h-6 w-16 mx-auto"></div></td>
      </tr>
    `;
  }
  tbody.innerHTML = html;
}
window.showAdminTableSkeleton = showAdminTableSkeleton;

/**
 * Mengambil daftar data seluruh formulir pengajuan request dari backend API (/api/admin/submissions).
 */
async function loadAdminSubmissions(showSkeleton = false) {
  const tbody = document.getElementById('adminTableBody');
  const loadingBar = document.getElementById('adminTableLoadingBar');
  const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

  if (loadingBar) loadingBar.classList.remove('hidden');
  if (showSkeleton && tbody && adminSubmissionsList.length === 0) {
    showAdminTableSkeleton(6);
  }

  try {
    const response = await fetch('/api/admin/submissions', {
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      }
    });
    const res = await response.json();

    if (res.success && res.data) {
      adminSubmissionsList = res.data;
      renderAdminTable(res.data);
    } else {
      if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">Gagal memuat data formulir.</td></tr>`;
    }
  } catch(err) {
    if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">Koneksi terputus ke server.</td></tr>`;
  } finally {
    if (loadingBar) loadingBar.classList.add('hidden');
  }
}
window.loadAdminSubmissions = loadAdminSubmissions;

/**
 * Me-render daftar baris pengajuan formulir ke dalam tabel dashboard admin.
 * 
 * @param {Array} data Array berisi objek submission pengajuan belanja
 */
function renderAdminTable(data) {
  const tbody = document.getElementById('adminTableBody');
  if (!tbody) return;

  if (!data || data.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="9" class="py-12 text-center text-slate-400">
          Belum ada data pengajuan formulir.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = data.map((sub, idx) => `
    <tr 
      class="hover:bg-indigo-50/50 transition-colors text-xs sm:text-sm text-slate-800 cursor-pointer select-none"
      onclick="openAdminModal(${sub.id})"
      title="Klik baris ini untuk membuka detail & review request #${sub.requestId || String(idx + 1).padStart(7, '0')}"
    >
      <td class="py-4 px-3 font-normal">${idx + 1}</td>
      <td class="py-4 px-4 font-normal font-mono font-medium text-slate-900">${sub.requestId || String(idx + 1).padStart(7, '0')}</td>
      <td class="py-4 px-4 font-normal">${sub.submittedBy?.username || sub.submittedBy?.name || 'uccloud26'}</td>
      <td class="py-4 px-4 font-normal font-medium">${sub.artistBand || sub.title || 'Taking Back Sunday'}</td>
      <td class="py-4 px-4 font-normal">${sub.merchType || sub.category || 'Vinyl'}</td>
      <td class="py-4 px-4 font-normal">${sub.releasedYear || '2009'}</td>
      <td class="py-4 px-4 font-normal">
        <span class="inline-flex items-center gap-1.5 font-medium">
          <span class="wm-status-dot ${statusDotClasses[sub.status] || 'wm-status-dot-process'}"></span>
          <span>${sub.status || 'In Progress'}</span>
        </span>
      </td>
      <td class="py-4 px-4 font-normal text-slate-500">${formatDate(sub.createdAt || sub.created_at)}</td>
      <td class="py-4 px-4 text-center whitespace-nowrap">
        <div class="flex items-center justify-center gap-1.5" onclick="event.stopPropagation()">
          <!-- Tombol Detail / Review Request -->
          <button
            type="button"
            onclick="openAdminModal(${sub.id})"
            class="p-1.5 rounded-lg text-slate-600 hover:text-[#4e46e5] hover:bg-indigo-50 transition-all cursor-pointer"
            title="Lihat Detail & Review Request #${escapeHtml(sub.requestId || String(idx + 1).padStart(7, '0'))}"
          >
            <i data-lucide="info" class="w-4.5 h-4.5"></i>
          </button>
          <!-- Tombol Hapus Request dari User (Permintaan Klien - Terkunci jika status Paid) -->
          ${(String(sub.status || '').toLowerCase().trim() === 'paid') ? `
          <button
            type="button"
            disabled
            class="p-1.5 rounded-lg text-slate-300 opacity-40 cursor-not-allowed"
            title="Pesanan sudah Paid (Lunas) dan terkunci sehingga tidak dapat dihapus"
          >
            <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
          </button>
          ` : `
          <button
            type="button"
            onclick="promptDeleteSubmission(${sub.id}, '${escapeHtml(sub.requestId || String(idx + 1).padStart(7, '0'))}')"
            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all cursor-pointer"
            title="Hapus Permintaan #${escapeHtml(sub.requestId || String(idx + 1).padStart(7, '0'))}"
          >
            <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
          </button>
          `}
        </div>
      </td>
    </tr>
  `).join('');

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.renderAdminTable = renderAdminTable;

/**
 * Variabel state untuk galeri foto di modal review admin
 * Foto User: Disimpan terpisah sebagai referensi pengajuan asli dari user
 * Foto Admin: Disimpan terpisah di bagian bawah (maksimal 3 foto temuan admin)
 */
let currentModalUserImage = '';
let currentModalAdminImages = [];
let currentModalImages = []; // Backward compatibility
let currentActiveImageIndex = 0;

/**
 * Membuka modal Lightbox Preview foto dalam ukuran besar
 * 
 * @param {string} imgSrc URL file gambar
 * @param {string} [title] Judul label foto
 */
function openPhotoLightbox(imgSrc, title) {
  if (!imgSrc) return;
  const modal = document.getElementById('photoLightboxModal');
  const img = document.getElementById('photoLightboxImg');
  const titleEl = document.getElementById('photoLightboxTitle');
  if (img) img.src = imgSrc;
  if (titleEl && title) titleEl.textContent = title;
  if (modal) modal.classList.remove('hidden');
  if (window.lucide) lucide.createIcons();
}
window.openPhotoLightbox = openPhotoLightbox;

/**
 * Menutup modal Lightbox Preview foto
 */
function closePhotoLightbox() {
  const modal = document.getElementById('photoLightboxModal');
  if (modal) modal.classList.add('hidden');
}
window.closePhotoLightbox = closePhotoLightbox;

/**
 * Mengganti gambar preview utama di galeri modal review ketika kotak thumbnail diklik.
 * 
 * @param {HTMLElement} el Elemen kotak thumbnail yang diklik
 * @param {string} [imgSrc] URL sumber gambar spesifik (opsional)
 * @param {number} [idx] Indeks gambar yang dipilih
 */
function selectThumbImage(el, imgSrc, idx) {
  if (typeof idx === 'number') {
    currentActiveImageIndex = idx;
  }
  const preview = document.getElementById('modalPreviewImg');
  if (preview) {
    if (imgSrc) {
      preview.src = imgSrc;
    } else {
      const img = el ? el.querySelector('img') : null;
      if (img) preview.src = img.src;
    }
  }
  if (imgSrc) {
    openPhotoLightbox(imgSrc, `Foto dari Admin ${(idx || 0) + 1}`);
  }
}
window.selectThumbImage = selectThumbImage;

let currentPreviewSource = 'user'; // 'user' atau 'admin_0', 'admin_1', 'admin_2'

/**
 * Mengganti tampilan gambar pada PREVIEW TUNGGAL di bagian atas modal review.
 * 
 * @param {string} src URL gambar yang akan ditampilkan pada preview utama
 * @param {'user'|'admin'} type Tipe sumber gambar ('user' atau 'admin')
 * @param {number} [idx] Indeks foto admin (jika tipe 'admin')
 */
function setModalMainPreview(src, type, idx) {
  if (!src) return;
  const mainImg = document.getElementById('modalMainPreviewImg');
  const userImg = document.getElementById('modalUserImg');
  const prevImg = document.getElementById('modalPreviewImg');

  if (mainImg) {
    mainImg.src = src;
    mainImg.style.transform = 'scale(1)';
    mainImg.style.transformOrigin = 'center center';
  }
  if (userImg) userImg.src = src;
  if (prevImg) prevImg.src = src;

  currentPreviewSource = (type === 'admin') ? `admin_${idx || 0}` : 'user';

  // Perbarui highlight active pada seluruh thumbnail (User row & Admin row)
  document.querySelectorAll('#adminModalUserPhotosRow .admin-thumb-box, #adminModalAdminPhotosRow .admin-thumb-box').forEach(b => {
    b.classList.remove('active');
  });

  if (type === 'user') {
    const userThumb = document.querySelector('#adminModalUserPhotosRow .admin-thumb-box');
    if (userThumb) userThumb.classList.add('active');
  } else if (type === 'admin') {
    const adminThumbs = document.querySelectorAll('#adminModalAdminPhotosRow .admin-thumb-box');
    if (adminThumbs && adminThumbs[idx || 0]) {
      adminThumbs[idx || 0].classList.add('active');
    }
  }
}
window.setModalMainPreview = setModalMainPreview;

/**
 * Memeriksa apakah status request masih sebelum 'In Progress' (yaitu masih 'Queue' / 'Quote' / 'Pending').
 * Aturan Klien: Admin hanya bisa menambahkan gambar ketika posisi status barang sudah 'In Progress'.
 * 
 * @param {string} status Nama status
 * @return {boolean} True jika status masih sebelum 'In Progress'
 */
function isStatusBeforeInProgress(status) {
  const s = String(status || '').toLowerCase().trim();
  return s === 'queue' || s === 'pending' || s === 'quote' || s === '';
}
window.isStatusBeforeInProgress = isStatusBeforeInProgress;

/**
 * Menampilkan pesan peringatan ketika admin mencoba menambah foto
 * sebelum status barang diubah menjadi 'In Progress'.
 */
function handleDisabledPhotoClick() {
  const currentStatus = (document.getElementById('modalStatus')?.value || 'Queue').trim();
  alert(`Foto temuan admin hanya dapat ditambahkan ketika posisi status barang sudah 'In Progress'.\n\nStatus barang saat ini masih '${currentStatus}'. Silakan ubah pilihan Status ke 'Process' / 'In Progress' pada formulir di sebelah kanan terlebih dahulu.`);
}
window.handleDisabledPhotoClick = handleDisabledPhotoClick;

/**
 * Me-render 2 Baris Foto di bawah Preview Tunggal:
 * 1. Baris 1: Foto dari User (thumbnail referensi pengajuan user).
 * 2. Baris 2: Foto dari Admin (thumbnails temuan admin + tombol Max 3 Photo).
 * 
 * Aturan Klien:
 * - Preview HANYA 1 di bagian atas (bukan preview-nya dua).
 * - Photo rows ada 2: satu dari user dan satu dari admin.
 * - Admin HANYA BISA menambahkan gambar ketika status SUDAH 'In Progress' / 'Process'.
 *   Ketika status masih 'Queue' / 'Quote', tidak bisa menambahkan foto (terkunci dengan ikon gembok).
 * - Tombol "Max 3 Photo" hanya muncul jika foto admin kurang dari 3.
 * - Gambar admin TIDAK BISA didelete oleh admin (tidak ada tombol delete).
 */
function renderAdminModalPhotos() {
  const userRow = document.getElementById('adminModalUserPhotosRow');
  const adminRow = document.getElementById('adminModalAdminPhotosRow');
  if (!userRow && !adminRow) return;

  const currentStatus = (document.getElementById('modalStatus')?.value || '').trim();
  const targetSub = selectedId ? adminSubmissionsList.find(s => s.id === selectedId) : null;
  const targetStatusLower = targetSub ? String(targetSub.status || '').toLowerCase().trim() : currentStatus.toLowerCase();
  // Kunci foto HANYA jika data pengajuan di database sebelumnya memang SUDAH berstatus 'Paid' atau 'Cancel'
  const isPaid = (targetStatusLower === 'paid');
  const isCancelled = ['cancel', 'cancelled', 'batal', 'rejected'].includes(targetStatusLower);
  const isLocked = isPaid || isCancelled;
  const isBefore = isStatusBeforeInProgress(currentStatus);
  const canAdd = currentModalAdminImages.length < 3;

  // 1. Render BARIS FOTO 1: Foto dari User (dengan tombol hapus jika status bukan Paid / Cancel)
  if (userRow) {
    const isUserActive = (currentPreviewSource === 'user');
    if (currentModalUserImage && currentModalUserImage !== '/assets/poster_art.png') {
      userRow.innerHTML = `
        <div 
          class="admin-thumb-box ${isUserActive ? 'active' : ''}" 
          onclick="setModalMainPreview('${escapeHtml(currentModalUserImage)}', 'user')"
          title="Foto Pengajuan dari User (Klik untuk melihat di preview atas)"
        >
          <img src="${escapeHtml(currentModalUserImage)}" alt="Foto dari User" onerror="this.src='/assets/poster_art.png'" />
          ${!isLocked ? `
          <button 
            type="button" 
            class="admin-thumb-delete-btn" 
            onclick="event.stopPropagation(); removeAdminModalUserPhoto()"
            title="Hapus foto pengajuan dari user"
            aria-label="Hapus foto pengajuan dari user"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 6h18"></path>
              <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
              <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </button>
          ` : ''}
        </div>
      `;
    } else {
      userRow.innerHTML = `
        ${!isLocked ? `
        <label 
          for="adminUserPhotoUploadInput" 
          class="admin-thumb-upload-box cursor-pointer" 
          title="Klik untuk mengunggah foto referensi user"
        >
          <i data-lucide="upload" class="w-3.5 h-3.5 text-white"></i>
          <span>Upload</span>
          <input 
            type="file" 
            id="adminUserPhotoUploadInput" 
            accept="image/*" 
            class="hidden" 
            onchange="handleAdminUserPhotoUpload(this)" 
          />
        </label>
        ` : `
        <span class="text-xs text-slate-400 italic py-1.5">Tidak ada foto dari user</span>
        `}
      `;
    }
  }

  // 2. Render BARIS FOTO 2: Foto dari Admin (Persis Screenshot 2 thumbnail row)
  const countTag = document.getElementById('adminPhotoCountTag');
  if (countTag) {
    countTag.textContent = `(${currentModalAdminImages.length}/3 Foto)`;
  }

  if (adminRow) {
    let adminHtml = '';

    // Thumbnail masing-masing foto temuan admin (dengan tombol hapus untuk admin, disabled jika Paid / Cancel)
    currentModalAdminImages.forEach((src, i) => {
      const isAdminActive = (currentPreviewSource === `admin_${i}`);
      adminHtml += `
        <div 
          class="admin-thumb-box ${isAdminActive ? 'active' : ''}" 
          onclick="setModalMainPreview('${escapeHtml(src)}', 'admin', ${i})"
          title="Foto dari Admin ${i + 1} (Klik untuk melihat di preview atas)"
        >
          <img src="${escapeHtml(src)}" alt="Foto Admin ${i + 1}" onerror="this.src='/assets/poster_art.png'" />
          ${!isLocked ? `
          <button 
            type="button" 
            class="admin-thumb-delete-btn" 
            onclick="event.stopPropagation(); removeAdminModalPhoto(${i})"
            title="Hapus foto admin ${i + 1}"
            aria-label="Hapus foto admin ${i + 1}"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 6h18"></path>
              <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
              <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </button>
          ` : ''}
        </div>
      `;
    });

    // Tombol "Max 3 Photo" untuk menambah foto admin (hanya muncul jika foto admin < 3)
    if (canAdd) {
      if (isLocked) {
        // Status sudah 'Paid' atau 'Cancel' -> Tombol disabled terkunci
        const lockLabel = isCancelled ? 'Canceled' : 'Paid';
        const lockMsg = isCancelled
          ? 'Pesanan sudah dibatalkan (Cancel). Foto terkunci.'
          : 'Pesanan sudah berstatus Paid (Lunas). Foto terkunci.';
        adminHtml += `
          <div 
            class="admin-thumb-upload-box disabled opacity-50 cursor-not-allowed" 
            id="adminThumbUploadBox" 
            title="${escapeHtml(lockMsg)}"
          >
            <i data-lucide="lock" class="w-3.5 h-3.5 text-white/70"></i>
            <span>${lockLabel}</span>
          </div>
        `;
      } else if (isBefore) {
        // Posisi status masih 'Queue' / 'Quote' -> Tombol disabled dengan ikon gembok
        adminHtml += `
          <div 
            class="admin-thumb-upload-box disabled" 
            id="adminThumbUploadBox" 
            onclick="handleDisabledPhotoClick()"
            title="Foto temuan admin hanya dapat ditambahkan ketika posisi status barang sudah 'In Progress'. Status saat ini masih '${escapeHtml(currentStatus)}'."
          >
            <i data-lucide="lock" class="w-3.5 h-3.5 text-white/70"></i>
            <span>Max 3 Photo</span>
          </div>
        `;
      } else {
        // Posisi status sudah 'In Progress' / 'Process' -> Admin bisa menambahkan gambar
        adminHtml += `
          <label 
            for="adminAddPhotoInput" 
            class="admin-thumb-upload-box cursor-pointer" 
            id="adminThumbUploadBox" 
            title="Klik untuk menambah foto temuan admin (Maksimal 3 Foto)"
          >
            <i data-lucide="upload" class="w-3.5 h-3.5 text-white"></i>
            <span>Max 3 Photo</span>
            <input 
              type="file" 
              id="adminAddPhotoInput" 
              accept="image/*" 
              class="hidden" 
              onchange="handleAdminAddPhoto(this)" 
            />
          </label>
        `;
      }
    }
    adminRow.innerHTML = adminHtml;
  }

  // Backward compatibility: sinkronkan ke container lama jika ada di DOM
  const oldThumbsRow = document.getElementById('adminModalThumbsRow');
  if (oldThumbsRow && adminRow) {
    oldThumbsRow.innerHTML = adminRow.innerHTML;
  }

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.renderAdminModalPhotos = renderAdminModalPhotos;
window.renderAdminModalThumbs = renderAdminModalPhotos; // Alias backward compatibility
window.renderAdminGallery = renderAdminModalPhotos;

/**
 * Menghapus foto temuan admin pada indeks tertentu dari modal review.
 * Memperbarui state currentModalAdminImages, menyesuaikan preview utama jika foto yang dihapus
 * sedang ditampilkan, dan me-render ulang thumbnail foto admin.
 * 
 * @param {number} index Indeks foto admin yang akan dihapus (0, 1, atau 2)
 */
function removeAdminModalPhoto(index) {
  if (index < 0 || index >= currentModalAdminImages.length) return;

  const currentStatus = (document.getElementById('modalStatus')?.value || '').trim();
  if (currentStatus.toLowerCase() === 'paid') {
    if (typeof window.showToast === 'function') {
      window.showToast('Pesanan sudah berstatus Paid (Lunas). Foto tidak dapat dihapus.');
    } else {
      alert('Pesanan sudah berstatus Paid (Lunas). Foto tidak dapat dihapus.');
    }
    return;
  }

  // Hapus dari array foto admin
  currentModalAdminImages.splice(index, 1);
  currentModalImages = [...currentModalAdminImages]; // Backward compatibility

  // Sesuaikan preview utama jika yang sedang aktif adalah foto yang baru saja dihapus
  if (currentPreviewSource === `admin_${index}`) {
    if (currentModalAdminImages.length > 0) {
      // Tampilkan foto admin yang tersisa pada indeks terdekat
      const nextIdx = Math.min(index, currentModalAdminImages.length - 1);
      setModalMainPreview(currentModalAdminImages[nextIdx], 'admin', nextIdx);
    } else {
      // Jika seluruh foto admin telah dihapus, kembalikan ke foto user
      setModalMainPreview(currentModalUserImage, 'user');
    }
  } else if (currentPreviewSource && currentPreviewSource.startsWith('admin_')) {
    // Jika preview aktif adalah foto admin dengan indeks setelahnya, geser indeksnya
    const curIdx = parseInt(currentPreviewSource.replace('admin_', ''), 10);
    if (!isNaN(curIdx) && curIdx > index) {
      currentPreviewSource = `admin_${curIdx - 1}`;
    }
  }

  // Render ulang thumbnails dan upload box (jika < 3, tombol upload Max 3 Photo akan muncul kembali)
  renderAdminModalPhotos();

  if (typeof window.showToast === 'function') {
    window.showToast('Foto temuan admin berhasil dihapus.');
  }
}
window.removeAdminModalPhoto = removeAdminModalPhoto;

/**
 * Menghapus foto referensi pengajuan dari user di modal review admin.
 * Hanya diizinkan jika status BUKAN 'Paid'.
 */
function removeAdminModalUserPhoto() {
  const currentStatus = (document.getElementById('modalStatus')?.value || '').trim();
  if (currentStatus.toLowerCase() === 'paid') {
    if (typeof window.showToast === 'function') {
      window.showToast('Pesanan sudah berstatus Paid (Lunas). Foto tidak dapat dihapus.');
    } else {
      alert('Pesanan sudah berstatus Paid (Lunas). Foto tidak dapat dihapus.');
    }
    return;
  }

  currentModalUserImage = '';

  // Jika preview utama sedang menampilkan foto user, alihkan ke foto admin pertama jika ada, atau placeholder
  if (currentPreviewSource === 'user') {
    if (currentModalAdminImages.length > 0) {
      setModalMainPreview(currentModalAdminImages[0], 'admin', 0);
    } else {
      setModalMainPreview('/assets/poster_art.png', 'user');
    }
  }

  renderAdminModalPhotos();

  if (typeof window.showToast === 'function') {
    window.showToast('Foto dari user berhasil dihapus.');
  }
}
window.removeAdminModalUserPhoto = removeAdminModalUserPhoto;

/**
 * Mengunggah atau mengganti foto referensi user oleh admin jika kosong/dihapus.
 */
function handleAdminUserPhotoUpload(input) {
  if (!input.files || !input.files[0]) return;

  const currentStatus = (document.getElementById('modalStatus')?.value || '').trim();
  if (currentStatus.toLowerCase() === 'paid') {
    alert('Pesanan sudah berstatus Paid (Lunas). Tidak dapat menambah foto.');
    input.value = '';
    return;
  }

  const file = input.files[0];
  if (!file.type.startsWith('image/')) {
    alert('Mohon pilih file gambar yang valid (JPG, PNG, WEBP).');
    input.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    const dataUrl = e.target.result;
    currentModalUserImage = dataUrl;
    renderAdminModalPhotos();
    setModalMainPreview(dataUrl, 'user');
    input.value = '';
  };
  reader.readAsDataURL(file);
}
window.handleAdminUserPhotoUpload = handleAdminUserPhotoUpload;

/**
 * Menangani pemilihan file foto tambahan oleh admin.
 * Membaca file sebagai Data URL dan menambahkannya ke daftar foto admin secara instan.
 * Memvalidasi bahwa posisi status barang sudah 'In Progress' dan belum melebihi 3 foto.
 * 
 * @param {HTMLInputElement} input Elemen input file
 */
function handleAdminAddPhoto(input) {
  if (!input.files || !input.files[0]) return;

  const currentStatus = (document.getElementById('modalStatus')?.value || '').trim();
  if (isStatusBeforeInProgress(currentStatus)) {
    handleDisabledPhotoClick();
    input.value = '';
    return;
  }

  if (currentModalAdminImages.length >= 3) {
    alert('Maksimal foto dari admin untuk request ini adalah 3 foto.');
    input.value = '';
    return;
  }

  const file = input.files[0];
  if (!file.type.startsWith('image/')) {
    alert('Mohon pilih file gambar yang valid (JPG, PNG, WEBP).');
    input.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    const dataUrl = e.target.result;
    currentModalAdminImages.push(dataUrl);
    currentModalImages = [...currentModalAdminImages]; // Backward compatibility
    renderAdminModalPhotos();
    setModalMainPreview(dataUrl, 'admin', currentModalAdminImages.length - 1);
    input.value = '';
  };
  reader.readAsDataURL(file);
}
window.handleAdminAddPhoto = handleAdminAddPhoto;

/**
 * Membuka modal review admin untuk pengajuan dengan ID tertentu.
 * Mem-populate data pemohon, detail merch, status, harga (mata uang & nominal), serta catatan seller.
 * 
 * @param {number} id ID pengajuan belanja
 */
function openAdminModal(id) {
  const sub = adminSubmissionsList.find(s => s.id === id);
  if (!sub) return;

  selectedId = sub.id;
  selectedRequestId = sub.requestId || sub.request_id || String(sub.id).padStart(7, '0');
  
  const headingEl = document.getElementById('modalHeading');
  if (headingEl) headingEl.textContent = `#${selectedRequestId}`;

  const currentStatus = sub.status || 'Queue';
  const topStatusText = document.getElementById('modalTopStatusText');
  const topStatusDot = document.getElementById('modalTopStatusDot');
  const foundAtEl = document.getElementById('modalFoundAt');

  if (topStatusText) topStatusText.textContent = currentStatus;
  if (topStatusDot) {
    topStatusDot.className = 'wm-status-dot ' + (statusDotClasses[currentStatus] || 'wm-status-dot-queue');
  }
  if (foundAtEl) {
    foundAtEl.textContent = sub.foundAt || formatDate(sub.createdAt || sub.created_at);
  }

  const userEl = document.getElementById('infoUser');
  if (userEl) userEl.textContent = sub.submittedBy?.username || sub.submittedBy?.name || sub.userName || '-';

  const fullNameEl = document.getElementById('infoFullName');
  if (fullNameEl) fullNameEl.textContent = (sub.fullName && sub.fullName !== 'bukan manusia biasa') ? sub.fullName : (sub.submittedBy?.name || sub.userName || '-');

  const addressEl = document.getElementById('infoAddress');
  if (addressEl) addressEl.textContent = (sub.address && !sub.address.includes('xxxxxxx')) ? sub.address : '-';

  const postalEl = document.getElementById('infoPostalCode');
  if (postalEl) postalEl.textContent = (sub.postalCode && sub.postalCode !== '1945') ? sub.postalCode : '-';

  const phoneEl = document.getElementById('infoPhone');
  if (phoneEl) phoneEl.textContent = (sub.phoneNumber && sub.phoneNumber !== '0813 888 8888') ? sub.phoneNumber : (sub.contact || '-');

  const artistEl = document.getElementById('infoArtistBand');
  if (artistEl) artistEl.textContent = sub.artistBand || sub.title || 'Taylor Swift';

  const merchTypeEl = document.getElementById('infoMerchType');
  if (merchTypeEl) merchTypeEl.textContent = sub.merchType || sub.category || 'T-Shirt';

  const yearEl = document.getElementById('infoReleasedYear');
  if (yearEl) yearEl.textContent = sub.releasedYear || '2016';

  const editionEl = document.getElementById('infoEdition');
  if (editionEl) editionEl.textContent = sub.merchEdition || 'Taylor Swift North American Tour';

  const descEl = document.getElementById('infoDesc');
  if (descEl) descEl.textContent = sub.description || 'Taylor Swift North American Tour 2016, Long Sleeve, White with tour list';

  selectStatusOption(sub.status || 'In Progress');

  // Parse harga lama menjadi mata uang dan nominal terpisah
  const rawPrice = (sub.price && sub.price !== '-') ? String(sub.price).trim() : '';
  let detectedCurrency = sub.currency || 'IDR';
  let detectedAmount = '';

  if (rawPrice) {
    if (rawPrice.startsWith('$') || rawPrice.toUpperCase().includes('USD')) {
      detectedCurrency = 'USD';
    } else if (rawPrice.startsWith('€') || rawPrice.toUpperCase().includes('EUR')) {
      detectedCurrency = 'EUR';
    } else if (rawPrice.startsWith('£') || rawPrice.toUpperCase().includes('GBP')) {
      detectedCurrency = 'GBP';
    } else if (rawPrice.startsWith('¥') || rawPrice.toUpperCase().includes('JPY')) {
      detectedCurrency = 'JPY';
    } else if (rawPrice.startsWith('S$') || rawPrice.toUpperCase().includes('SGD')) {
      detectedCurrency = 'SGD';
    } else if (rawPrice.startsWith('A$') || rawPrice.toUpperCase().includes('AUD')) {
      detectedCurrency = 'AUD';
    } else if (rawPrice.startsWith('RM') || rawPrice.toUpperCase().includes('MYR')) {
      detectedCurrency = 'MYR';
    } else if (rawPrice.startsWith('Rp') || rawPrice.toUpperCase().includes('IDR')) {
      detectedCurrency = 'IDR';
    }

    // Ambil bagian angka numerik saja
    const numOnly = rawPrice.replace(/[^0-9]/g, '');
    if (numOnly) {
      detectedAmount = new Intl.NumberFormat('id-ID').format(numOnly);
    }
  } else if (sub.priceAmount) {
    detectedAmount = new Intl.NumberFormat('id-ID').format(String(sub.priceAmount).replace(/[^0-9]/g, ''));
  }

  const sym = currencySymbols[detectedCurrency] || 'Rp';
  selectCurrencyOption(detectedCurrency, sym);
  const priceAmountEl = document.getElementById('modalPriceAmount');
  if (priceAmountEl) priceAmountEl.value = detectedAmount;
  updateModalPriceValue();

  const notesEl = document.getElementById('modalNotes');
  if (notesEl) notesEl.value = sub.sellerNotes && sub.sellerNotes !== '-' ? sub.sellerNotes : (sub.adminNotes || '-');

  // Pisahkan secara tegas foto user (referensi awal) dan foto admin (temuan barang)
  currentModalUserImage = sub.userImage || sub.imageUrl || sub.image_url || '/assets/poster_art.png';

  if (Array.isArray(sub.adminImages) && sub.adminImages.length > 0) {
    currentModalAdminImages = [...sub.adminImages];
  } else if (Array.isArray(sub.admin_images) && sub.admin_images.length > 0) {
    currentModalAdminImages = [...sub.admin_images];
  } else if (Array.isArray(sub.images) && sub.images.length > 0) {
    // Pastikan foto user TIDAK masuk ke daftar foto admin
    currentModalAdminImages = sub.images.filter(img => img && img !== currentModalUserImage);
  } else {
    currentModalAdminImages = [];
  }
  currentModalImages = [...currentModalAdminImages];

  // Set preview tunggal di atas default menampilkan foto user
  currentPreviewSource = 'user';
  const mainPreviewImg = document.getElementById('modalMainPreviewImg');
  if (mainPreviewImg) {
    mainPreviewImg.src = currentModalUserImage;
    mainPreviewImg.onerror = function() { this.src = '/assets/poster_art.png'; };
  }
  const userImgEl = document.getElementById('modalUserImg');
  if (userImgEl) userImgEl.src = currentModalUserImage;
  const previewImg = document.getElementById('modalPreviewImg');
  if (previewImg) previewImg.src = currentModalUserImage;

  // Render kedua baris foto (Baris 1 Foto User & Baris 2 Foto Admin)
  renderAdminModalPhotos();

  // Kunci kontrol modal HANYA jika status pengajuan di database memang SUDAH 'Paid' atau 'Cancel'
  const statusLower = String(sub.status || '').toLowerCase().trim();
  const isAlreadyPaid = (statusLower === 'paid');
  const isAlreadyCancelled = ['cancel', 'cancelled', 'batal', 'rejected'].includes(statusLower);
  const isLocked = isAlreadyPaid || isAlreadyCancelled;
  const lockReason = isAlreadyCancelled ? 'cancel' : 'paid';
  updateAdminModalLockState(isLocked, lockReason);

  const reviewModal = document.getElementById('adminReviewModal');
  if (reviewModal) reviewModal.classList.remove('hidden');

  // Aktifkan efek dynamic cursor zoom pada preview gambar utama
  setupAdminModalDynamicZoom();

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.openAdminModal = openAdminModal;

/**
 * Inisialisasi efek Dynamic Cursor Zoom pada preview gambar utama modal admin review.
 * Saat kursor mouse digerakkan di atas gambar, gambar akan membesar (zoom in 2.2x)
 * dan titik fokus pembesaran (transform-origin) mengikuti posisi kursor secara dinamis 60fps.
 */
function setupAdminModalDynamicZoom() {
  const wrap = document.querySelector('.admin-modal-main-img-wrap');
  const img = document.getElementById('modalMainPreviewImg');
  if (!wrap || !img) return;

  if (wrap.dataset.zoomReady === 'true') return;
  wrap.dataset.zoomReady = 'true';

  const scaleFactor = 2.2;
  let isHovered = false;

  wrap.addEventListener('mouseenter', () => {
    isHovered = true;
    img.style.transition = 'transform 0.15s ease-out';
    img.style.transform = `scale(${scaleFactor})`;
  });

  wrap.addEventListener('mousemove', (e) => {
    if (!isHovered) return;
    const rect = wrap.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const xPercent = Math.max(0, Math.min(100, (x / rect.width) * 100));
    const yPercent = Math.max(0, Math.min(100, (y / rect.height) * 100));

    img.style.transition = 'transform 0.04s ease-out';
    img.style.transformOrigin = `${xPercent.toFixed(1)}% ${yPercent.toFixed(1)}%`;
    img.style.transform = `scale(${scaleFactor})`;
  });

  wrap.addEventListener('mouseleave', () => {
    isHovered = false;
    img.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform-origin 0.25s ease';
    img.style.transformOrigin = 'center center';
    img.style.transform = 'scale(1)';
  });
}
window.setupAdminModalDynamicZoom = setupAdminModalDynamicZoom;

/**
 * Menutup modal review admin dan mereset ID pengajuan terpilih.
 */
function closeAdminModal() {
  const reviewModal = document.getElementById('adminReviewModal');
  if (reviewModal) reviewModal.classList.add('hidden');
  closeAdminConfirmModal();
  updateAdminModalLockState(false);
  selectedId = null;
  selectedRequestId = null;
}
window.closeAdminModal = closeAdminModal;

/**
 * Menampilkan pop-up modal konfirmasi makesure ("Are you sure want to save changes?")
 * sebelum menyimpan perubahan status dan data penawaran review admin.
 */
function promptConfirmAdminSave() {
  if (!selectedId) return;

  // Jika pesanan sudah 'Paid' atau 'Cancel', cegah penyimpanan karena sudah terkunci
  if (selectedId) {
    const sub = adminSubmissionsList.find(s => s.id === selectedId);
    const subStatusLower = String(sub?.status || '').toLowerCase().trim();
    if (subStatusLower === 'paid') {
      alert('Pesanan ini sudah Paid (Lunas) dan terkunci.');
      return;
    }
    if (['cancel', 'cancelled', 'batal', 'rejected'].includes(subStatusLower)) {
      alert('Pesanan ini sudah dibatalkan (Cancel) dan terkunci sehingga tidak dapat diedit kembali.');
      return;
    }
  }

  const overlay = document.getElementById('adminConfirmModalOverlay');
  if (overlay) {
    overlay.classList.add('active');
  }
}
window.promptConfirmAdminSave = promptConfirmAdminSave;

/**
 * Menutup pop-up modal konfirmasi simpan admin.
 */
function closeAdminConfirmModal() {
  const overlay = document.getElementById('adminConfirmModalOverlay');
  if (overlay) {
    overlay.classList.remove('active');
  }
}
window.closeAdminConfirmModal = closeAdminConfirmModal;

/**
 * Menangani klik pada area latar belakang (backdrop) modal konfirmasi.
 * 
 * @param {MouseEvent} event Event klik mouse
 */
function handleAdminConfirmBackdrop(event) {
  if (event.target === document.getElementById('adminConfirmModalOverlay')) {
    closeAdminConfirmModal();
  }
}
window.handleAdminConfirmBackdrop = handleAdminConfirmBackdrop;

/**
 * Menjalankan penyimpanan review admin saat tombol "Yes" pada modal konfirmasi diklik.
 */
function executeAdminSave() {
  closeAdminConfirmModal();
  submitStatusUpdate();
}
window.executeAdminSave = executeAdminSave;

/**
 * Variabel state untuk menyimpan ID dan Request ID submission yang akan dihapus
 */
let submissionToDeleteId = null;
let submissionToDeleteRequestId = '';

/**
 * Membuka modal dialog konfirmasi penghapusan request pengajuan belanja dari user.
 * 
 * @param {number|string} id ID unik record pengajuan di database
 * @param {string} requestId Format nomor Request ID (contoh: 0000001)
 */
function promptDeleteSubmission(id, requestId) {
  // Cegah penghapusan jika status pengajuan sudah Paid
  const targetSub = adminSubmissionsList.find(s => s.id === id);
  if (targetSub && String(targetSub.status || '').toLowerCase().trim() === 'paid') {
    if (typeof window.showToast === 'function') {
      window.showToast('Permintaan dengan status Paid (Lunas) tidak dapat dihapus.');
    } else {
      alert('Permintaan dengan status Paid (Lunas) tidak dapat dihapus.');
    }
    return;
  }

  submissionToDeleteId = id;
  submissionToDeleteRequestId = requestId || String(id).padStart(7, '0');

  const textEl = document.getElementById('deleteSubmissionRequestIdText');
  if (textEl) textEl.textContent = '#' + submissionToDeleteRequestId;

  const modal = document.getElementById('adminDeleteConfirmOverlay');
  if (modal) {
    modal.classList.add('active');
  }
}
window.promptDeleteSubmission = promptDeleteSubmission;

/**
 * Menutup modal dialog konfirmasi penghapusan request pengajuan belanja.
 */
function closeAdminDeleteConfirmModal() {
  const modal = document.getElementById('adminDeleteConfirmOverlay');
  if (modal) {
    modal.classList.remove('active');
  }
  submissionToDeleteId = null;
  submissionToDeleteRequestId = '';
}
window.closeAdminDeleteConfirmModal = closeAdminDeleteConfirmModal;

/**
 * Menangani klik backdrop pada modal konfirmasi hapus request agar tertutup
 * jika pengguna mengklik area luar kartu modal.
 * 
 * @param {MouseEvent} event Event klik mouse
 */
function handleAdminDeleteConfirmBackdrop(event) {
  if (event.target === document.getElementById('adminDeleteConfirmOverlay')) {
    closeAdminDeleteConfirmModal();
  }
}
window.handleAdminDeleteConfirmBackdrop = handleAdminDeleteConfirmBackdrop;

/**
 * Mengeksekusi request DELETE ke server backend (/api/admin/submissions/{id}) untuk menghapus pengajuan.
 * Menampilkan animasi loading, notifikasi toast sukses, dan memperbarui tabel dashboard admin.
 */
async function executeDeleteSubmission() {
  if (!submissionToDeleteId) return;

  // Double validation: Cegah penghapusan jika status Paid
  const targetSub = adminSubmissionsList.find(s => s.id === submissionToDeleteId);
  if (targetSub && String(targetSub.status || '').toLowerCase().trim() === 'paid') {
    alert('Permintaan dengan status Paid (Lunas) tidak dapat dihapus.');
    closeAdminDeleteConfirmModal();
    return;
  }

  const targetId = submissionToDeleteId;
  const targetReqId = submissionToDeleteRequestId;
  const btn = document.getElementById('btnConfirmDeleteSubmission');
  const originalBtnText = btn ? btn.innerHTML : 'Ya, Hapus';

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin inline-block mr-1"></i> Menghapus...`;
    if (window.lucide) lucide.createIcons();
  }

  try {
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    const response = await fetch(`/api/admin/submissions/${targetId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      }
    });

    const res = await response.json();

    if (res.success) {
      closeAdminDeleteConfirmModal();

      // Hapus data dari array lokal dan re-render tabel secara instan
      adminSubmissionsList = adminSubmissionsList.filter(s => s.id !== targetId);
      renderAdminTable(adminSubmissionsList);

      if (typeof window.showToast === 'function') {
        window.showToast(`Permintaan #${targetReqId} berhasil dihapus dari sistem.`);
      } else {
        alert(`Permintaan #${targetReqId} berhasil dihapus dari sistem.`);
      }

      // Muat ulang dari backend untuk memastikan sinkronisasi sempurna
      loadAdminSubmissions();
    } else {
      alert(res.message || 'Gagal menghapus formulir pengajuan.');
    }
  } catch (err) {
    alert('Terjadi kesalahan jaringan saat mencoba menghapus pengajuan.');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalBtnText;
      if (window.lucide) lucide.createIcons();
    }
  }
}
window.executeDeleteSubmission = executeDeleteSubmission;

// Tutup modal konfirmasi atau modal review saat tombol Escape ditekan
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const deleteOverlay = document.getElementById('adminDeleteConfirmOverlay');
    if (deleteOverlay && deleteOverlay.classList.contains('active')) {
      closeAdminDeleteConfirmModal();
      return;
    }
    const confirmOverlay = document.getElementById('adminConfirmModalOverlay');
    if (confirmOverlay && confirmOverlay.classList.contains('active')) {
      closeAdminConfirmModal();
      return;
    }
    const reviewModal = document.getElementById('adminReviewModal');
    if (reviewModal && !reviewModal.classList.contains('hidden')) {
      closeAdminModal();
    }
  }
});

/**
 * Mengirim hasil review pengajuan (Status baru, Harga tertera, Catatan) ke server backend
 * via endpoint PATCH /api/admin/submissions/{id}/status.
 */
async function submitStatusUpdate() {
  if (!selectedId) return;

  // Double validation: Cegah penyimpanan jika pengajuan di database sudah Paid atau Cancel
  const targetSub = adminSubmissionsList.find(s => s.id === selectedId);
  const targetStatusLower = String(targetSub?.status || '').toLowerCase().trim();
  if (targetStatusLower === 'paid') {
    alert('Pesanan ini sudah Paid (Lunas) dan terkunci.');
    return;
  }
  if (['cancel', 'cancelled', 'batal', 'rejected'].includes(targetStatusLower)) {
    alert('Pesanan ini sudah dibatalkan (Cancel) dan terkunci sehingga tidak dapat diedit kembali.');
    return;
  }

  // Simpan Request ID dan ID target terlebih dahulu sebelum modal ditutup dan variable di-reset
  const displayRequestId = selectedRequestId || String(selectedId).padStart(7, '0');
  const targetId = selectedId;

  const newStatus = document.getElementById('modalStatus').value;
  updateModalPriceValue();
  const curr = document.getElementById('modalCurrency').value;
  const nominal = document.getElementById('modalPriceAmount').value.trim();
  const price = document.getElementById('modalPrice').value.trim();
  const sellerNotes = document.getElementById('modalNotes').value.trim();

  const btn = document.getElementById('btnSaveModal');
  const label = document.getElementById('labelSaveModal');
  const spin = document.getElementById('spinSaveModal');

  if (btn) btn.disabled = true;
  if (label) label.textContent = 'Saving...';
  if (spin) spin.classList.remove('hidden');

  try {
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    const response = await fetch(`/api/admin/submissions/${targetId}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({
        status: newStatus,
        price: price,
        currency: curr,
        priceAmount: nominal,
        price_amount: nominal,
        sellerNotes: sellerNotes,
        seller_notes: sellerNotes,
        adminNotes: sellerNotes,
        admin_notes: sellerNotes,
        admin_images: currentModalAdminImages,
        images: currentModalAdminImages,
        user_image: currentModalUserImage
      })
    });

    const res = await response.json();

    if (res.success) {
      const updatedAdminImages = (res.data && res.data.adminImages) 
        ? res.data.adminImages 
        : ((res.data && res.data.images) ? res.data.images : currentModalAdminImages);

      // Update daftar lokal di adminSubmissionsList
      const targetSub = adminSubmissionsList.find(s => s.id === targetId);
      if (targetSub) {
        targetSub.adminImages = updatedAdminImages;
        targetSub.admin_images = updatedAdminImages;
        targetSub.images = updatedAdminImages;
        targetSub.userImage = currentModalUserImage;
      }

      // Simpan perubahan ke localStorage agar halaman Merch Request List user langsung tersinkronkan
      try {
        localStorage.setItem('wm_admin_last_updated', JSON.stringify({
          id: targetId,
          requestId: displayRequestId,
          status: newStatus,
          price: price,
          currency: curr,
          priceAmount: nominal,
          sellerNotes: sellerNotes,
          userImage: currentModalUserImage,
          adminImages: updatedAdminImages,
          images: updatedAdminImages,
          timestamp: Date.now()
        }));
      } catch(e) {}

      closeAdminModal();
      loadAdminSubmissions();
      if (typeof window.showToast === 'function') {
        window.showToast(`Request #${displayRequestId} berhasil diperbarui.`);
      }
    } else {
      alert(res.message || 'Gagal menyimpan status.');
    }
  } catch(e) {
    alert('Terjadi kesalahan jaringan.');
  } finally {
    if (btn) btn.disabled = false;
    if (label) label.textContent = 'Save Changes';
    if (spin) spin.classList.add('hidden');
    if (window.lucide) lucide.createIcons();
  }
}
window.submitStatusUpdate = submitStatusUpdate;

/**
 * Menampilkan atau menyembunyikan karakter password pada form tambah admin.
 * 
 * @param {string} inputId ID elemen input password
 * @param {string} iconId ID elemen ikon Lucide
 */
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.setAttribute('data-lucide', 'eye-off');
  } else {
    input.type = 'password';
    icon.setAttribute('data-lucide', 'eye');
  }

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.togglePassword = togglePassword;

/**
 * Membuka Modal Tambah Admin Baru (Khusus Superadmin).
 * 
 * Alur kerja:
 * - Menutup dropdown navigasi header jika sedang terbuka.
 * - Mereset form pendaftaran dan menyembunyikan alert error/sukses sebelumnya.
 * - Menampilkan modal pendaftaran admin baru dengan animasi scale-up.
 * - Memperbarui rendering icon Lucide.
 */
function openAddAdminModal() {
  const d = document.getElementById('adminDropdown');
  if (d) d.classList.add('hidden');
  const modal = document.getElementById('superadminAddAdminModal');
  const err = document.getElementById('addAdminErrorAlert');
  const succ = document.getElementById('addAdminSuccessAlert');
  const form = document.getElementById('addAdminForm');

  if (err) err.classList.add('hidden');
  if (succ) succ.classList.add('hidden');
  if (form) form.reset();

  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.openAddAdminModal = openAddAdminModal;

/**
 * Menutup Modal Tambah Admin Baru dan menyembunyikannya dari tampilan.
 */
function closeAddAdminModal() {
  const modal = document.getElementById('superadminAddAdminModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
}
window.closeAddAdminModal = closeAddAdminModal;

/**
 * Mengirimkan data pendaftaran admin baru ke backend endpoint POST /api/admin/admins.
 * 
 * Aturan Bisnis & Validasi:
 * - Hanya dapat dieksekusi oleh pengguna dengan hak akses Superadmin.
 * - Kolom Email, Username, Password, dan Re-type Password wajib diisi.
 * - Panjang password minimal 6 karakter dan harus sama persis dengan konfirmasi.
 * - Pilihan role: 'admin' (Admin Biasa) atau 'superadmin'.
 * - Setelah berhasil, otomatis mereset form, menyinkronkan data ke tabel Manage Accounts,
 *   dan menutup modal secara halus.
 */
async function submitCreateAdmin() {
  const err = document.getElementById('addAdminErrorAlert');
  const errMsg = document.getElementById('addAdminErrorMsg');
  const succ = document.getElementById('addAdminSuccessAlert');
  const succMsg = document.getElementById('addAdminSuccessMsg');
  const btn = document.getElementById('btnAddAdminSubmit');
  const label = document.getElementById('labelAddAdmin');
  const spin = document.getElementById('spinAddAdmin');

  if (err) err.classList.add('hidden');
  if (succ) succ.classList.add('hidden');

  const email = document.getElementById('newAdminEmail')?.value.trim();
  const username = document.getElementById('newAdminUsername')?.value.trim();
  const role = document.getElementById('newAdminRole')?.value || 'admin';
  const password = document.getElementById('newAdminPassword')?.value;
  const confirmPassword = document.getElementById('newAdminConfirmPassword')?.value;

  if (!email || !username || !password) {
    if (errMsg) errMsg.textContent = 'Seluruh kolom (Email, Username, Password) wajib diisi.';
    if (err) err.classList.remove('hidden');
    return;
  }

  if (password.length < 6) {
    if (errMsg) errMsg.textContent = 'Password minimal 6 karakter.';
    if (err) err.classList.remove('hidden');
    return;
  }

  if (password !== confirmPassword) {
    if (errMsg) errMsg.textContent = 'Konfirmasi password (Re-type Password) tidak cocok.';
    if (err) err.classList.remove('hidden');
    return;
  }

  const currentUser = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

  if (btn) btn.disabled = true;
  if (label) label.textContent = 'Mendaftarkan...';
  if (spin) spin.classList.remove('hidden');

  try {
    const res = await fetch('/api/admin/admins', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({
        email: email,
        username: username,
        password: password,
        role: 'admin',
        caller_email: currentUser?.email,
        active_user_id: currentUser?.id
      })
    });

    const data = await res.json();

    if (data.success) {
      if (succMsg) succMsg.textContent = data.message || `Admin baru (${username}) berhasil didaftarkan sebagai Admin!`;
      if (succ) succ.classList.remove('hidden');

      const form = document.getElementById('addAdminForm');
      if (form) form.reset();

      // Refresh tabel manage accounts jika sedang dimuat
      if (typeof window.fetchManageAccounts === 'function') {
        window.fetchManageAccounts();
      }

      setTimeout(() => {
        closeAddAdminModal();
      }, 1600);
    } else {
      if (errMsg) errMsg.textContent = data.message || 'Gagal menambahkan admin baru.';
      if (err) err.classList.remove('hidden');
    }
  } catch(e) {
    if (errMsg) errMsg.textContent = 'Terjadi kesalahan jaringan atau server.';
    if (err) err.classList.remove('hidden');
  } finally {
    if (btn) btn.disabled = false;
    if (label) label.textContent = 'Daftarkan Admin';
    if (spin) spin.classList.add('hidden');
    if (window.lucide) lucide.createIcons();
  }
}
window.submitCreateAdmin = submitCreateAdmin;

// ============================================================================
// FITUR KHUSUS SUPERADMIN: MANAGE ACCOUNTS (KELOLA AKUN USER & ADMIN)
// ============================================================================
let allAccountsList = [];
let currentAccountFilter = 'all';

/**
 * Helper untuk sanitasi teks agar aman saat disisipkan ke HTML (Mencegah serangan XSS).
 * 
 * @param {string} str Teks mentah yang akan disanitasi
 * @return {string} Teks yang aman untuk disisipkan ke dalam innerHTML
 */
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
window.escapeHtml = escapeHtml;

/**
 * Menutup modal admin (Manage Accounts / Tambah Admin) ketika pengguna
 * mengklik area gelap (backdrop) di luar kartu modal utama.
 * 
 * @param {MouseEvent} e Objek event klik dari browser
 */
function handleAdminModalBackdrop(e) {
  if (e.target === e.currentTarget) {
    closeAddAdminModal();
    closeManageAccountsModal();
  }
}
window.handleAdminModalBackdrop = handleAdminModalBackdrop;

/**
 * Membuka Modal Manage Accounts khusus bagi Superadmin.
 * Fungsi ini otomatis menutup menu dropdown di header dan memicu
 * pengambilan data akun pengguna & admin terbaru dari server (/api/admin/accounts).
 */
function openManageAccountsModal() {
  const d = document.getElementById('adminDropdown');
  if (d) d.classList.add('hidden');
  const modal = document.getElementById('superadminManageAccountsModal');
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    fetchManageAccounts();
  }
}
window.openManageAccountsModal = openManageAccountsModal;

/**
 * Menutup Modal Manage Accounts dan menyembunyikannya dari tampilan layar.
 */
function closeManageAccountsModal() {
  const modal = document.getElementById('superadminManageAccountsModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
}
window.closeManageAccountsModal = closeManageAccountsModal;

/**
 * Mengambil daftar seluruh akun (User & Admin) dari server via endpoint GET /api/admin/accounts.
 * Menghitung jumlah akun per kategori (Semua, Admin, User), memperbarui label indikator tab,
 * serta menampilkan data ke dalam tabel akun modal.
 */
async function fetchManageAccounts() {
  const tbody = document.getElementById('manageAccountsTableBody');
  const alertEl = document.getElementById('manageAccountsAlert');
  if (alertEl) alertEl.classList.add('hidden');

  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="py-12 text-center text-slate-400">
          <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto text-[#4e46e5] mb-2"></i>
          Memuat daftar akun...
        </td>
      </tr>
    `;
    if (window.lucide) lucide.createIcons();
  }

  const currentUser = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

  try {
    const url = new URL('/api/admin/accounts', window.location.origin);
    if (currentUser?.email) url.searchParams.set('caller_email', currentUser.email);
    if (currentUser?.id) url.searchParams.set('active_user_id', currentUser.id);

    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      }
    });

    const data = await res.json();
    if (data.success && Array.isArray(data.data)) {
      allAccountsList = data.data;
      const isSuperadmin = (data.is_superadmin !== undefined) ? data.is_superadmin : (currentUser && (currentUser.role === 'superadmin' || currentUser.email?.includes('superadmin')));
      window.isSuperadminSession = isSuperadmin;

      // Sembunyikan atau tampilkan tombol tambah admin inline dan tab admin
      const btnAddInline = document.getElementById('btnAddAdminInline');
      const tabAdmin = document.getElementById('filterTabAdmin');

      if (!isSuperadmin) {
        if (btnAddInline) btnAddInline.classList.add('hidden');
        if (tabAdmin) tabAdmin.classList.add('hidden');
      } else {
        if (btnAddInline) btnAddInline.classList.remove('hidden');
        if (tabAdmin) tabAdmin.classList.remove('hidden');
      }

      // Update badge counts on tabs
      const countAll = document.getElementById('countTabAll');
      const countAdmin = document.getElementById('countTabAdmin');
      const countUser = document.getElementById('countTabUser');
      const summaryText = document.getElementById('manageAccountsSummaryText');

      const adminCount = allAccountsList.filter(u => u.role === 'admin' || u.role === 'superadmin').length;
      const userCount = allAccountsList.filter(u => u.role === 'user').length;

      if (countAll) countAll.textContent = allAccountsList.length;
      if (countAdmin) countAdmin.textContent = adminCount;
      if (countUser) countUser.textContent = userCount;
      if (summaryText) {
        if (isSuperadmin) {
          summaryText.textContent = `Total ${allAccountsList.length} akun terdaftar (${adminCount} Admin, ${userCount} User)`;
        } else {
          summaryText.textContent = `Total ${allAccountsList.length} akun pengguna (User) terdaftar`;
        }
      }

      applyAccountsFilter();
    } else {
      if (tbody) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="py-10 text-center text-rose-500 font-semibold">
              ${data.message || 'Gagal memuat data akun.'}
            </td>
          </tr>
        `;
      }
    }
  } catch(e) {
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="py-10 text-center text-rose-500 font-semibold">
            Terjadi kesalahan jaringan saat mengambil data akun.
          </td>
        </tr>
      `;
    }
  }
}
window.fetchManageAccounts = fetchManageAccounts;

/**
 * Mengubah filter tab kategori akun ('all', 'admin', 'user') dan memperbarui tampilan tab aktif.
 * 
 * @param {string} role Kategori role yang dipilih ('all' | 'admin' | 'user')
 */
function filterAccountsByRole(role) {
  currentAccountFilter = role;
  ['filterTabAll', 'filterTabAdmin', 'filterTabUser'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.className = 'admin-tab-btn';
    }
  });

  const activeTabId = role === 'admin' ? 'filterTabAdmin' : (role === 'user' ? 'filterTabUser' : 'filterTabAll');
  const activeTab = document.getElementById(activeTabId);
  if (activeTab) {
    activeTab.className = 'admin-tab-btn active';
  }

  applyAccountsFilter();
}
window.filterAccountsByRole = filterAccountsByRole;

/**
 * Menangani pencarian teks langsung (live-search) nama akun atau email.
 */
function handleAccountsSearch() {
  applyAccountsFilter();
}
window.handleAccountsSearch = handleAccountsSearch;

/**
 * Menerapkan kombinasi filter tab kategori dan kata kunci pencarian ke daftar akun lokal
 * sebelum ditampilkan pada tabel HTML.
 */
function applyAccountsFilter() {
  const searchInput = document.getElementById('manageAccountsSearch');
  const keyword = searchInput ? searchInput.value.trim().toLowerCase() : '';

  let filtered = allAccountsList;

  // Filter berdasarkan kategori role
  if (currentAccountFilter === 'admin') {
    filtered = filtered.filter(u => u.role === 'admin' || u.role === 'superadmin');
  } else if (currentAccountFilter === 'user') {
    filtered = filtered.filter(u => u.role === 'user');
  }

  // Filter berdasarkan kata kunci pencarian nama atau email
  if (keyword) {
    filtered = filtered.filter(u => {
      const name = (u.name || '').toLowerCase();
      const fullName = (u.full_name || '').toLowerCase();
      const email = (u.email || '').toLowerCase();
      return name.includes(keyword) || fullName.includes(keyword) || email.includes(keyword);
    });
  }

  renderAccountsTable(filtered);
}

/**
 * Merender baris tabel akun di dalam modal Manage Accounts menggunakan styling
 * tabel bawaan project (.admin-table) yang konsisten dengan halaman Admin Dashboard.
 * 
 * @param {Array<Object>} list Daftar data akun yang telah difilter
 */
function renderAccountsTable(list) {
  const tbody = document.getElementById('manageAccountsTableBody');
  if (!tbody) return;

  if (list.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="py-12 text-center text-slate-400">
          <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
          Tidak ditemukan akun yang sesuai dengan filter atau pencarian.
        </td>
      </tr>
    `;
    if (window.lucide) lucide.createIcons();
    return;
  }

  const currentUser = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;

  tbody.innerHTML = list.map((u, idx) => {
    const isSelf = currentUser && (currentUser.id == u.id || (currentUser.email && currentUser.email.toLowerCase() === u.email.toLowerCase()));
    const isSuperadminAccount = u.role === 'superadmin' || ['superadmin@wastingmoney.id'].includes((u.email || '').toLowerCase());

    let roleBadge = '';
    if (u.role === 'superadmin') {
      roleBadge = '<span class="admin-role-badge-superadmin">Superadmin</span>';
    } else if (u.role === 'admin') {
      roleBadge = '<span class="admin-role-badge-admin">Admin</span>';
    } else {
      roleBadge = '<span class="admin-role-badge-user">User</span>';
    }

    const displayName = u.full_name ? `${escapeHtml(u.full_name)} <span class="text-slate-400 font-normal">(@${escapeHtml(u.name)})</span>` : escapeHtml(u.name);
    const initial = (u.name || 'U').charAt(0).toUpperCase();

    let actionControls = '';
    const isSuperadmin = (window.isSuperadminSession !== undefined) ? window.isSuperadminSession : (currentUser && (currentUser.role === 'superadmin' || currentUser.email?.includes('superadmin')));

    if (isSuperadminAccount) {
      // Akun Superadmin hanya boleh 1 dalam sistem, terlindungi dan tidak dapat diubah/dihapus
      actionControls = `<span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-lg">Superadmin Utama</span>`;
    } else if (!isSuperadmin) {
      // Admin Biasa: hanya dapat mengelola (hapus) akun User, tidak dapat mengubah role admin
      actionControls = `
        <div class="flex items-center justify-center gap-2">
          ${!isSelf ? `
            <button
              type="button"
              onclick="handleDeleteUser(${u.id}, '${escapeHtml(u.name)}')"
              class="admin-btn-delete-user"
              title="Hapus Akun Pengguna ${escapeHtml(u.name)}"
            >
              <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
          ` : `
            <span class="text-[10px] text-indigo-600 font-bold px-1.5 py-0.5 bg-indigo-50 rounded">Akun Anda</span>
          `}
        </div>
      `;
    } else {
      // Superadmin: mengelola akun lain (hanya dapat toggle antara User dan Admin, Superadmin tidak dapat dibuat)
      const roleDisplayName = u.role === 'admin' ? 'Admin' : 'User';
      actionControls = `
        <div class="flex items-center justify-center gap-2">
          ${!isSelf ? `
            <div class="wm-dropdown-wrapper relative" id="userRoleWrapper_${u.id}">
              <button
                type="button"
                id="userRoleBtn_${u.id}"
                onclick="event.stopPropagation(); toggleDropdownMenu('userRoleMenu_${u.id}', 'userRoleBtn_${u.id}')"
                class="admin-table-role-dropdown-trigger"
                title="Pilih role untuk akun ${escapeHtml(u.name)}"
              >
                <span id="userRoleLabel_${u.id}" class="capitalize">${roleDisplayName}</span>
                <i data-lucide="chevron-down" class="wm-dropdown-chevron w-3.5 h-3.5 text-slate-500"></i>
              </button>

              <div class="wm-dropdown-menu admin-table-role-menu" id="userRoleMenu_${u.id}">
                <div
                  class="wm-dropdown-item py-1.5 px-2.5 text-xs flex items-center justify-between cursor-pointer ${u.role === 'user' ? 'selected' : ''}"
                  onclick="selectAccountRole(${u.id}, 'user', 'User')"
                >
                  <span class="font-medium text-slate-700">User</span>
                  <i data-lucide="check" class="wm-dropdown-check w-3.5 h-3.5 ${u.role === 'user' ? '' : 'hidden'}"></i>
                </div>
                <div
                  class="wm-dropdown-item py-1.5 px-2.5 text-xs flex items-center justify-between cursor-pointer ${u.role === 'admin' ? 'selected' : ''}"
                  onclick="selectAccountRole(${u.id}, 'admin', 'Admin')"
                >
                  <span class="font-medium text-slate-700">Admin</span>
                  <i data-lucide="check" class="wm-dropdown-check w-3.5 h-3.5 ${u.role === 'admin' ? '' : 'hidden'}"></i>
                </div>
              </div>
            </div>
          ` : `
            <span class="text-[11px] text-indigo-600 font-bold px-2 py-1 bg-indigo-50 border border-indigo-200 rounded-lg">Akun Anda (${roleDisplayName})</span>
          `}

          ${!isSelf ? `
            <button
              type="button"
              onclick="handleDeleteUser(${u.id}, '${escapeHtml(u.name)}')"
              class="admin-btn-delete-user"
              title="Hapus Akun ${escapeHtml(u.name)}"
            >
              <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
          ` : ''}
        </div>
      `;
    }

    return `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="py-3 px-3 text-center font-semibold text-slate-400">${idx + 1}</td>
        <td class="py-3 px-4">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-600">
              ${initial}
            </div>
            <div>
              <p class="font-bold text-slate-900 leading-tight">${displayName}</p>
            </div>
          </div>
        </td>
        <td class="py-3 px-4 text-slate-600 font-mono text-xs">${escapeHtml(u.email)}</td>
        <td class="py-3 px-4">${roleBadge}</td>
        <td class="py-3 px-4 text-center">${actionControls}</td>
      </tr>
    `;
  }).join('');

  if (window.lucide) lucide.createIcons();
}

/**
 * Memilih opsi role baru untuk suatu akun dari custom dropdown dan mengirim pembaruan ke server.
 * 
 * @param {number} userId ID akun yang akan diubah role-nya
 * @param {string} newRole Role baru yang dipilih ('user' | 'admin' | 'superadmin')
 * @param {string} roleLabel Label tampilan role yang ramah pengguna
 */
function selectAccountRole(userId, newRole, roleLabel) {
  // Tutup dropdown menu setelah opsi dipilih
  const menu = document.getElementById('userRoleMenu_' + userId);
  const btn = document.getElementById('userRoleBtn_' + userId);
  if (menu) menu.classList.remove('show');
  if (btn) btn.classList.remove('open');

  const labelEl = document.getElementById('userRoleLabel_' + userId);
  if (labelEl) labelEl.textContent = roleLabel || newRole;

  handleUpdateUserRole(userId, newRole);
}
window.selectAccountRole = selectAccountRole;

/**
 * Handler pembaruan role akun secara realtime oleh Superadmin.
 * Mengirimkan HTTP PATCH ke /api/admin/accounts/{id}/role dan menampilkan alert konfirmasi.
 * 
 * @param {number} userId ID pengguna yang akan diubah role-nya
 * @param {string} newRole Role baru yang dipilih ('user' | 'admin' | 'superadmin')
 */
async function handleUpdateUserRole(userId, newRole) {
  const currentUser = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

  try {
    const res = await fetch(`/api/admin/accounts/${userId}/role`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({
        role: newRole,
        caller_email: currentUser?.email,
        active_user_id: currentUser?.id
      })
    });

    const data = await res.json();
    if (data.success) {
      showAccountsAlert(data.message || `Role akun berhasil diubah menjadi ${newRole}`, 'success');
      const acc = allAccountsList.find(a => a.id === userId);
      if (acc) acc.role = newRole;
      applyAccountsFilter();
    } else {
      showAccountsAlert(data.message || 'Gagal mengubah role akun.', 'error');
      fetchManageAccounts();
    }
  } catch(e) {
    showAccountsAlert('Terjadi kesalahan jaringan saat memperbarui role.', 'error');
    fetchManageAccounts();
  }
}
window.handleUpdateUserRole = handleUpdateUserRole;

/**
 * Handler penghapusan akun oleh Superadmin dengan konfirmasi ganda.
 * Mengirimkan HTTP DELETE ke /api/admin/accounts/{id}.
 * 
 * @param {number} userId ID akun yang akan dihapus
 * @param {string} userName Nama atau username akun untuk pesan konfirmasi
 */
async function handleDeleteUser(userId, userName) {
  if (!confirm(`Apakah Anda yakin ingin menghapus akun "${userName}"? Tindakan ini tidak dapat dibatalkan.`)) {
    return;
  }

  const currentUser = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

  try {
    const res = await fetch(`/api/admin/accounts/${userId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({
        caller_email: currentUser?.email,
        active_user_id: currentUser?.id
      })
    });

    const data = await res.json();
    if (data.success) {
      showAccountsAlert(data.message || `Akun ${userName} berhasil dihapus.`, 'success');
      allAccountsList = allAccountsList.filter(a => a.id !== userId);
      applyAccountsFilter();
    } else {
      showAccountsAlert(data.message || 'Gagal menghapus akun.', 'error');
    }
  } catch(e) {
    showAccountsAlert('Terjadi kesalahan jaringan saat menghapus akun.', 'error');
  }
}
window.handleDeleteUser = handleDeleteUser;

/**
 * Menampilkan pesan alert pemberitahuan (sukses/gagal) pada modal Manage Accounts.
 * 
 * @param {string} msg Pesan teks notifikasi
 * @param {'success'|'error'} [type='success'] Jenis status alert
 */
function showAccountsAlert(msg, type = 'success') {
  const alertEl = document.getElementById('manageAccountsAlert');
  const msgEl = document.getElementById('manageAccountsAlertMsg');
  if (!alertEl || !msgEl) return;

  msgEl.textContent = msg;
  if (type === 'success') {
    alertEl.className = 'mx-6 mt-3 p-3 rounded-xl text-xs flex items-center justify-between gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800';
  } else {
    alertEl.className = 'mx-6 mt-3 p-3 rounded-xl text-xs flex items-center justify-between gap-2 bg-rose-50 border border-rose-200 text-rose-800';
  }
  alertEl.classList.remove('hidden');

  setTimeout(() => {
    alertEl.classList.add('hidden');
  }, 4000);
}

/**
 * Menutup pesan alert notifikasi secara manual.
 */
function dismissAccountsAlert() {
  const alertEl = document.getElementById('manageAccountsAlert');
  if (alertEl) alertEl.classList.add('hidden');
}
window.dismissAccountsAlert = dismissAccountsAlert;

/**
 * Menyinkronkan identitas administrator yang sedang aktif di header dan dashboard.
 * Membedakan akses antara Superadmin dan Admin Biasa:
 * - Admin Biasa: hanya halaman admin dan tombol Logout di dropdown.
 * - Superadmin: memiliki Manage Account dan Tambah Admin Baru.
 */
function syncAdminInfo() {
  const authState = document.getElementById('adminAuthState');
  const unauthState = document.getElementById('adminUnauthState');
  const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;

  if (user) {
    const isSuperAdmin = user.role === 'superadmin' || (user.email && user.email.includes('superadmin'));
    const isAdmin = user.role === 'admin' || isSuperAdmin || (user.email && user.email.includes('admin'));

    if (isAdmin) {
      const nameEl = document.getElementById('adminDropdownName');
      const emailEl = document.getElementById('adminDropdownEmail');
      const btnManageAccountHeader = document.getElementById('adminDropdownBtnManageAccount');
      const btnManageAccountTop = document.getElementById('btnOpenManageAccountsModal');

      if (nameEl) nameEl.textContent = user.full_name || user.name || user.username || 'Administrator';
      if (emailEl) emailEl.textContent = user.email || 'admin@rakapteman.id';

      // Kedua level administrator (Admin Biasa & Superadmin) dapat membuka Manage Account
      if (btnManageAccountHeader) btnManageAccountHeader.classList.remove('hidden');
      if (btnManageAccountTop) btnManageAccountTop.classList.remove('hidden');

      if (authState && unauthState) {
        authState.classList.remove('hidden');
        unauthState.classList.add('hidden');
      }
      return;
    }
  }

  if (authState && unauthState) {
    authState.classList.add('hidden');
    unauthState.classList.remove('hidden');
  }
}
window.syncAdminInfo = syncAdminInfo;

// Auto-Sync Realtime Polling untuk Admin Dashboard setiap 4 detik
let adminSyncInterval = null;
function startAdminLiveSync() {
  if (adminSyncInterval) clearInterval(adminSyncInterval);
  adminSyncInterval = setInterval(async () => {
    if (document.hidden) return;
    // Jangan overwrite jika admin sedang membuka modal review atau sedang mengetik
    const modalBackdrop = document.getElementById('adminModalBackdrop');
    if (modalBackdrop && !modalBackdrop.classList.contains('hidden')) {
      return;
    }
    await loadAdminSubmissions();
  }, 4000);
}
window.startAdminLiveSync = startAdminLiveSync;

// Jalankan saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
  syncAdminInfo();
  loadAdminSubmissions();
  startAdminLiveSync();
});
