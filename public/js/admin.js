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
function selectStatusOption(status) {
  const input = document.getElementById('modalStatus');
  const textEl = document.getElementById('statusTriggerText');
  const dotEl = document.getElementById('statusTriggerDot');
  
  if (input) input.value = status;
  if (textEl) textEl.textContent = status;
  if (dotEl) {
    dotEl.className = 'wm-status-dot ' + (statusDotClasses[status] || 'wm-status-dot-in-progress');
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
 * Mengambil daftar data seluruh formulir pengajuan request dari backend API (/api/admin/submissions).
 */
async function loadAdminSubmissions() {
  const tbody = document.getElementById('adminTableBody');
  const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

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
      <td class="py-4 px-3 text-center">
        <button
          type="button"
          onclick="event.stopPropagation(); openAdminModal(${sub.id})"
          class="p-1 text-slate-700 hover:text-[#4e46e5] hover:scale-110 transition-all cursor-pointer"
          title="Lihat Detail & Review"
        >
          <i data-lucide="info" class="w-4.5 h-4.5"></i>
        </button>
      </td>
    </tr>
  `).join('');

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.renderAdminTable = renderAdminTable;

/**
 * Mengganti gambar preview utama di galeri modal review ketika kotak thumbnail diklik.
 * 
 * @param {HTMLElement} el Elemen kotak thumbnail yang diklik
 */
function selectThumbImage(el) {
  const img = el.querySelector('img');
  if (img) {
    const preview = document.getElementById('modalPreviewImg');
    if (preview) preview.src = img.src;
  }
}
window.selectThumbImage = selectThumbImage;

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

  const foundAtEl = document.getElementById('modalFoundAt');
  if (foundAtEl) foundAtEl.textContent = `Found ${sub.foundAt || '10/11/2026 10:08:55'}`;

  const userEl = document.getElementById('infoUser');
  if (userEl) userEl.textContent = sub.submittedBy?.username || sub.submittedBy?.name || 'uccloud26';

  const fullNameEl = document.getElementById('infoFullName');
  if (fullNameEl) fullNameEl.textContent = sub.fullName || 'bukan manusia biasa';

  const addressEl = document.getElementById('infoAddress');
  if (addressEl) addressEl.textContent = sub.address || 'Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx';

  const postalEl = document.getElementById('infoPostalCode');
  if (postalEl) postalEl.textContent = sub.postalCode || '1945';

  const phoneEl = document.getElementById('infoPhone');
  if (phoneEl) phoneEl.textContent = sub.phoneNumber || '0813 888 8888';

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

  const mainImg = sub.imageUrl || '/assets/poster_art.png';
  const previewImg = document.getElementById('modalPreviewImg');
  if (previewImg) previewImg.src = mainImg;

  ['thumb1', 'thumb2', 'thumb3'].forEach(id => {
    const t = document.getElementById(id);
    if (t) t.src = mainImg;
  });

  const reviewModal = document.getElementById('adminReviewModal');
  if (reviewModal) reviewModal.classList.remove('hidden');

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.openAdminModal = openAdminModal;

/**
 * Menutup modal review admin dan mereset ID pengajuan terpilih.
 */
function closeAdminModal() {
  const reviewModal = document.getElementById('adminReviewModal');
  if (reviewModal) reviewModal.classList.add('hidden');
  closeAdminConfirmModal();
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

// Tutup modal konfirmasi atau modal review saat tombol Escape ditekan
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const confirmOverlay = document.getElementById('adminConfirmModalOverlay');
    if (confirmOverlay && confirmOverlay.classList.contains('active')) {
      closeAdminConfirmModal();
    } else {
      const reviewModal = document.getElementById('adminReviewModal');
      if (reviewModal && !reviewModal.classList.contains('hidden')) {
        closeAdminModal();
      }
    }
  }
});

/**
 * Mengirim hasil review pengajuan (Status baru, Harga tertera, Catatan) ke server backend
 * via endpoint PATCH /api/admin/submissions/{id}/status.
 */
async function submitStatusUpdate() {
  if (!selectedId) return;

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
        admin_notes: sellerNotes
      })
    });

    const res = await response.json();

    if (res.success) {
      // Simpan perubahan ke localStorage agar halaman user list / cart langsung tersinkronkan
      try {
        localStorage.setItem('wm_admin_last_updated', JSON.stringify({
          id: targetId,
          requestId: displayRequestId,
          status: newStatus,
          price: price,
          currency: curr,
          priceAmount: nominal,
          sellerNotes: sellerNotes,
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
 * Menyinkronkan identitas administrator yang sedang aktif di header.
 */
function syncAdminInfo() {
  const authState = document.getElementById('adminAuthState');
  const unauthState = document.getElementById('adminUnauthState');
  const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;

  if (user) {
    const isAdmin = user.role === 'admin' || (user.email && user.email.includes('admin'));
    if (isAdmin) {
      const nameEl = document.getElementById('adminDropdownName');
      const emailEl = document.getElementById('adminDropdownEmail');
      if (nameEl) nameEl.textContent = user.full_name || user.name || user.username || 'Administrator';
      if (emailEl) emailEl.textContent = user.email || 'admin@rakapteman.id';
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

// Jalankan saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
  syncAdminInfo();
  loadAdminSubmissions();
});
