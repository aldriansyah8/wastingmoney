/**
 * wastingmoney - User Request List Page Script
 * File: public/js/user_list.js
 * 
 * Modul JavaScript terpisah khusus untuk halaman daftar request user (/list).
 * Mengatur dataset kartu request (mockup + live submissions), pencarian & filter status,
 * pengurutan (sort A-Z / tanggal), serta modal interaktif dengan tema warna dinamis (Dynamic Template Color).
 */

// Inisialisasi ikon Lucide
if (window.lucide) {
  lucide.createIcons();
}

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
let selectedRequestItem = null;
let currentSortBy = 'default';
let sortOrderAsc = true;

/**
 * Inisialisasi halaman saat dokumen selesai dimuat.
 */
document.addEventListener('DOMContentLoaded', () => {
  checkUserAuth();
  loadLiveSubmissions();
  renderCards();
  updateFilterCounts();

  // Escape listener untuk menutup modal atau popover filter/sort
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeUserModal();
      closeFilterDropdown();
    }
  });

  // Menutup menu dropdown filter & sort jika pengguna mengklik di luar area menu
  document.addEventListener('click', (e) => {
    const filterWrapper = document.getElementById('filterDropdownWrapper');
    const sortWrapper = document.getElementById('sortDropdownWrapper');
    const filterMenu = document.getElementById('filterDropdownMenu');
    const sortMenu = document.getElementById('sortDropdownMenu');
    const filterBtn = document.getElementById('btnToggleFilterDropdown');

    if (filterWrapper && !filterWrapper.contains(e.target) && filterMenu && !filterMenu.classList.contains('hidden')) {
      filterMenu.classList.add('hidden');
      if (filterBtn) filterBtn.classList.remove('active');
    }

    if (sortWrapper && !sortWrapper.contains(e.target) && sortMenu && !sortMenu.classList.contains('hidden')) {
      sortMenu.classList.add('hidden');
    }
  });
});

/**
 * Memeriksa status otentikasi user dari getActiveUser() atau localStorage.
 * Menampilkan nama dan email di menu profile header jika sudah login.
 */
function checkUserAuth() {
  const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const authState = document.getElementById('userAuthState');
  const unauthState = document.getElementById('userUnauthState');

  if (user) {
    const nameEl = document.getElementById('userDropdownName');
    const emailEl = document.getElementById('userDropdownEmail');
    if (nameEl) nameEl.textContent = user.full_name || user.name || user.username || 'User';
    if (emailEl) emailEl.textContent = user.email || 'user@wastingmoney.id';
    if (authState) authState.classList.remove('hidden');
    if (unauthState) unauthState.classList.add('hidden');
  } else {
    if (authState) authState.classList.add('hidden');
    if (unauthState) unauthState.classList.remove('hidden');
  }
}
window.checkUserAuth = checkUserAuth;

/**
 * Menampilkan atau menyembunyikan menu pop-up dropdown user di header.
 */
function toggleUserDropdown() {
  const d = document.getElementById('userDropdown');
  if (d) d.classList.toggle('hidden');
}
window.toggleUserDropdown = toggleUserDropdown;

// Menutup menu dropdown user jika pengguna mengklik di luar area menu
document.addEventListener('click', (e) => {
  const wrapper = document.getElementById('userMenuWrapper');
  const d = document.getElementById('userDropdown');
  if (wrapper && !wrapper.contains(e.target) && d && !d.classList.contains('hidden')) {
    d.classList.add('hidden');
  }
});

/**
 * Helper untuk menentukan warna tema dan dot berdasarkan nama status.
 * Abu-abu = Queue / Request Baru
 * Kuning = Process / Tunggu Admin Approve
 * Hijau = Found! / Approved (Disetujui Admin)
 * Biru = Paid / Lanjut (Selesai/Lanjut Pembayaran)
 * Merah = Cancel / Dibatalkan
 * 
 * @param {string} status 
 * @returns {{ theme: string, dotClass: string, displayStatus: string }}
 */
function mapStatusToTheme(status) {
  const s = String(status || '').toLowerCase().trim();
  if (s.includes('found') || s.includes('approve')) {
    return { theme: 'theme-green', dotClass: 'dot-green', displayStatus: 'Found!' };
  } else if (s.includes('process') || s.includes('progress')) {
    return { theme: 'theme-yellow', dotClass: 'dot-yellow', displayStatus: 'Process' };
  } else if (s.includes('paid') || s.includes('lanjut')) {
    return { theme: 'theme-blue', dotClass: 'dot-blue', displayStatus: 'Paid' };
  } else if (s.includes('cancel') || s.includes('batal') || s.includes('reject')) {
    return { theme: 'theme-pink', dotClass: 'dot-pink', displayStatus: 'Cancel' };
  } else {
    // Default: Queue (Abu-abu / User Request)
    return { theme: 'theme-gray', dotClass: 'dot-gray', displayStatus: 'Queue' };
  }
}
window.mapStatusToTheme = mapStatusToTheme;

/**
 * Memuat request belanja baru milik user secara realtime baik dari database server (/api/submissions/my)
 * maupun localStorage, lalu menggabungkannya ke dalam daftar kartu.
 */
async function loadLiveSubmissions() {
  try {
    const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    let serverSubmissions = [];
    try {
      // Ambil seluruh submission dari database agar update status, harga, dan seller notes dari admin langsung sinkron
      const res = await fetch('/api/submissions', {
        headers: {
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        }
      });
      if (res.ok) {
        const json = await res.json();
        if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
          serverSubmissions = json.data;
        }
      }
    } catch(err) {
      console.log('Server /api/submissions offline or network error', err);
    }

    // Ambil data request yang dibuat lokal di browser
    const localSubmissions = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');

    // 1. Salin data default requests
    let merged = defaultUserRequests.map(item => ({ ...item }));

    // 2. Sinkronkan pembaruan dari database server
    serverSubmissions.forEach(sub => {
      const mapped = mapStatusToTheme(sub.status);
      const subNum = parseInt(String(sub.requestId || sub.id).replace(/[^0-9]/g, ''), 10);
      
      const target = merged.find(m => 
        m.id === sub.id || 
        parseInt(String(m.requestId).replace(/[^0-9]/g, ''), 10) === subNum
      );

      if (target) {
        // Update item yang sudah ada sesuai status, tema warna, harga & catatan yang diubah admin
        target.status = mapped.displayStatus;
        target.theme = mapped.theme;
        target.dotClass = mapped.dotClass;
        if (sub.price && sub.price !== '') target.price = sub.price;
        if (sub.sellerNotes && sub.sellerNotes !== '') target.sellerNotes = sub.sellerNotes;
        if (sub.artistBand) target.artistBand = sub.artistBand;
        if (sub.merchType) target.merchType = sub.merchType;
        if (sub.releasedYear) target.releasedYear = sub.releasedYear;
        if (sub.merchEdition) target.merchEdition = sub.merchEdition;
        if (sub.description) target.description = sub.description;
        if (sub.statusDate) target.statusDate = sub.statusDate;
      } else {
        // Jika submission baru dari user yang belum ada di 10 demo, tambahkan di awal
        merged.unshift({
          ...sub,
          status: mapped.displayStatus,
          theme: mapped.theme,
          dotClass: mapped.dotClass
        });
      }
    });

    // 3. Terapkan pembaruan terakhir admin dari localStorage (untuk sinkronisasi instan real-time)
    try {
      const lastAdminEdit = JSON.parse(localStorage.getItem('wm_admin_last_updated') || 'null');
      if (lastAdminEdit) {
        const adminNum = parseInt(String(lastAdminEdit.requestId || lastAdminEdit.id).replace(/[^0-9]/g, ''), 10);
        const target = merged.find(m => 
          m.id === lastAdminEdit.id || 
          parseInt(String(m.requestId).replace(/[^0-9]/g, ''), 10) === adminNum
        );
        if (target) {
          const mapped = mapStatusToTheme(lastAdminEdit.status);
          target.status = mapped.displayStatus;
          target.theme = mapped.theme;
          target.dotClass = mapped.dotClass;
          if (lastAdminEdit.price) target.price = lastAdminEdit.price;
          if (lastAdminEdit.sellerNotes) target.sellerNotes = lastAdminEdit.sellerNotes;
        }
      }
    } catch(e) {}

    // 4. Tambahkan submission lokal baru jika ada
    localSubmissions.forEach(sub => {
      const mapped = mapStatusToTheme(sub.status);
      const subNum = parseInt(String(sub.requestId || sub.id).replace(/[^0-9]/g, ''), 10);
      const exists = merged.some(m => 
        m.id === sub.id || 
        parseInt(String(m.requestId).replace(/[^0-9]/g, ''), 10) === subNum
      );
      if (!exists) {
        merged.unshift({
          ...sub,
          status: mapped.displayStatus,
          theme: mapped.theme,
          dotClass: mapped.dotClass
        });
      }
    });

    currentRequests = merged;
    renderCards();
    updateFilterCounts();
  } catch (e) {
    console.log('Error in loadLiveSubmissions', e);
  }
}
window.loadLiveSubmissions = loadLiveSubmissions;

// Listener event storage untuk pembaruan lintas tab real-time
window.addEventListener('storage', (event) => {
  if (event.key === 'wm_admin_last_updated' || event.key === 'wm_user_created_requests') {
    loadLiveSubmissions();
    if (selectedRequestItem) {
      const updated = currentRequests.find(r => r.id === selectedRequestItem.id);
      if (updated) {
        openUserModal(updated.id);
      }
    }
  }
});

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
    if (emptyState) emptyState.classList.remove('hidden');
    return;
  }

  if (emptyState) emptyState.classList.add('hidden');
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
window.renderCards = renderCards;

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
  const reqIdEl = document.getElementById('userModalReqId');
  if (reqIdEl) reqIdEl.textContent = item.requestId;
  
  const dotEl = document.getElementById('userModalStatusDot');
  if (dotEl) dotEl.className = `user-modal-status-dot ${item.dotClass}`;
  
  const statusTextEl = document.getElementById('userModalStatusText');
  if (statusTextEl) statusTextEl.textContent = `${item.status} ${item.statusDate}`;

  // 4. Tampilkan rincian informasi barang
  const artistEl = document.getElementById('userModalArtist');
  if (artistEl) artistEl.textContent = item.artistBand;

  const typeEl = document.getElementById('userModalMerchType');
  if (typeEl) typeEl.textContent = item.merchType;

  const yearEl = document.getElementById('userModalYear');
  if (yearEl) yearEl.textContent = item.releasedYear;

  const editionEl = document.getElementById('userModalEdition');
  if (editionEl) editionEl.textContent = item.merchEdition;

  const descEl = document.getElementById('userModalDesc');
  if (descEl) descEl.textContent = item.description;

  const priceEl = document.getElementById('userModalPrice');
  if (priceEl) priceEl.textContent = item.price;

  const notesEl = document.getElementById('userModalSellerNotes');
  if (notesEl) notesEl.textContent = item.sellerNotes;

  // 5. Render tombol aksi berdasarkan template status sesuai instruksi pengguna:
  // - Found! (Hijau): Tombol Export Invoice PDF + Tombol Lanjut (ubah jadi biru) + Tombol Cancel (ubah jadi merah)
  // - Queue (Abu-abu / User Request): Menunggu Admin + Cancel
  // - Process (Kuning / Tunggu Approve): Tunggu Approve + Cancel
  // - Paid (Biru / User Memilih Lanjut): Sudah Selesai + Unduh Invoice PDF + Tutup
  // - Cancel (Merah / User Cancel): Permintaan Dibatalkan + Tutup
  const actionsArea = document.getElementById('userModalActionsArea');
  if (actionsArea) {
    if (item.status === 'Found!') {
      // Status Hijau (Admin sudah Approve):
      // User dapat mengunduh Invoice PDF untuk mengabari admin, memilih Lanjut (menjadi Biru), atau Cancel (menjadi Merah)
      actionsArea.innerHTML = `
        <button 
          type="button" 
          class="user-btn-export-pdf" 
          onclick="exportInvoicePdf(${item.id})"
          title="Unduh Invoice PDF untuk mengabari admin"
        >
          <i data-lucide="file-down" class="w-4 h-4"></i>
          <span>Export Invoice (PDF)</span>
        </button>
        <button 
          type="button" 
          class="user-btn-proceed" 
          onclick="handleUserProceed(${item.id})"
          title="Lanjut pemesanan (warna kartu akan berubah menjadi biru)"
        >
          <i data-lucide="check-circle" class="w-4 h-4"></i>
          <span>Lanjut</span>
        </button>
        <button 
          type="button" 
          class="user-btn-cancel" 
          onclick="handleUserCancel(${item.id})"
          title="Batalkan pemesanan (warna kartu akan berubah menjadi merah)"
        >
          Cancel
        </button>
      `;
    } else if (item.status === 'Queue') {
      // Status Abu-abu: Request baru user, menunggu review admin
      actionsArea.innerHTML = `
        <button 
          type="button" 
          class="user-btn-pay-disabled" 
          onclick="showToast('Status: Request (Abu-Abu). Menunggu admin memproses request Anda.')"
          title="Menunggu Review Admin"
        >
          Menunggu Review
        </button>
        <button 
          type="button" 
          class="user-btn-cancel" 
          onclick="handleUserCancel(${item.id})"
          title="Batalkan permintaan"
        >
          Cancel
        </button>
      `;
    } else if (item.status === 'Process') {
      // Status Kuning: Sedang diproses / tunggu approval admin
      actionsArea.innerHTML = `
        <button 
          type="button" 
          class="user-btn-pay-disabled" 
          onclick="showToast('Status: In Process (Kuning). Sedang diproses admin, tunggu admin approve.')"
          title="Tunggu Admin Approve"
        >
          Tunggu Approve
        </button>
        <button 
          type="button" 
          class="user-btn-cancel" 
          onclick="handleUserCancel(${item.id})"
          title="Batalkan permintaan"
        >
          Cancel
        </button>
      `;
    } else if (item.status === 'Paid') {
      // Status Biru: Pengguna memilih Lanjut / sudah selesai
      actionsArea.innerHTML = `
        <div class="user-status-notice paid">
          <i data-lucide="check-circle" class="w-4 h-4"></i>
          <span>Pesanan Dilanjutkan (Selesai)</span>
        </div>
        <button 
          type="button" 
          class="user-btn-export-pdf" 
          onclick="exportInvoicePdf(${item.id})"
          title="Unduh Invoice PDF"
        >
          <i data-lucide="file-down" class="w-4 h-4"></i>
          <span>Unduh Invoice (PDF)</span>
        </button>
        <button 
          type="button" 
          class="user-btn-cancel" 
          onclick="closeUserModal()"
        >
          Tutup
        </button>
      `;
    } else if (item.status === 'Cancel') {
      // Status Merah: Permintaan dibatalkan pengguna atau admin
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
  if (overlay) overlay.classList.add('active');
  document.body.style.overflow = 'hidden';

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.openUserModal = openUserModal;

/**
 * Menutup modal pop-up detail dan mengaktifkan kembali scroll halaman.
 */
function closeUserModal() {
  const overlay = document.getElementById('userItemModalOverlay');
  if (overlay) overlay.classList.remove('active');
  document.body.style.overflow = '';
}
window.closeUserModal = closeUserModal;

/**
 * Menutup modal ketika pengguna mengklik area backdrop gelap di luar kartu modal.
 * 
 * @param {MouseEvent} e Event klik mouse
 */
function handleBackdropClick(e) {
  if (e.target && e.target.id === 'userItemModalOverlay') {
    closeUserModal();
  }
}
window.handleBackdropClick = handleBackdropClick;

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
  if (el) el.classList.add('selected');
}
window.switchModalThumb = switchModalThumb;

/**
 * Menangani aksi saat user memilih "Lanjut" pada status Hijau (Approved / Found!).
 * Mengubah status item menjadi "Paid" dan mengubah warna kartu menjadi BIRU secara otomatis.
 * 
 * @param {number} itemId ID dari item request yang dilanjutkan
 */
async function handleUserProceed(itemId) {
  const item = currentRequests.find(r => r.id === itemId);
  if (!item) return;

  item.status = 'Paid';
  item.theme = 'theme-blue';
  item.dotClass = 'dot-blue';
  item.statusDate = new Date().toLocaleDateString('id-ID') + ' ' + new Date().toLocaleTimeString('id-ID');

  // Update ke backend database jika ada ID valid
  try {
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    await fetch(`/api/submissions/${item.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({ status: 'Paid' })
    });
  } catch(e) {
    console.log('Saved to local session');
  }

  // Update di localStorage
  try {
    const local = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');
    const targetIdx = local.findIndex(l => l.id === item.id || l.requestId === item.requestId);
    if (targetIdx !== -1) {
      local[targetIdx].status = 'Paid';
      local[targetIdx].theme = 'theme-blue';
      local[targetIdx].dotClass = 'dot-blue';
      localStorage.setItem('wm_user_created_requests', JSON.stringify(local));
    }
  } catch(e) {}

  renderCards();
  updateFilterCounts();
  openUserModal(item.id);

  if (typeof window.showToast === 'function') {
    window.showToast('Pesanan berhasil dilanjutkan! Warna pesanan berubah menjadi Biru.');
  }
}
window.handleUserProceed = handleUserProceed;

/**
 * Menangani aksi saat user memilih "Cancel" pada item.
 * Mengubah status item menjadi "Cancel" dan mengubah warna kartu menjadi MERAH secara otomatis.
 * 
 * @param {number} itemId ID dari item request yang dibatalkan
 */
async function handleUserCancel(itemId) {
  const item = currentRequests.find(r => r.id === itemId);
  if (!item) return;

  item.status = 'Cancel';
  item.theme = 'theme-pink';
  item.dotClass = 'dot-pink';
  item.statusDate = new Date().toLocaleDateString('id-ID') + ' ' + new Date().toLocaleTimeString('id-ID');

  // Update ke backend database jika ada ID valid
  try {
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');
    await fetch(`/api/submissions/${item.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({ status: 'Cancel' })
    });
  } catch(e) {
    console.log('Saved to local session');
  }

  // Update di localStorage
  try {
    const local = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');
    const targetIdx = local.findIndex(l => l.id === item.id || l.requestId === item.requestId);
    if (targetIdx !== -1) {
      local[targetIdx].status = 'Cancel';
      local[targetIdx].theme = 'theme-pink';
      local[targetIdx].dotClass = 'dot-pink';
      localStorage.setItem('wm_user_created_requests', JSON.stringify(local));
    }
  } catch(e) {}

  renderCards();
  updateFilterCounts();
  openUserModal(item.id);

  if (typeof window.showToast === 'function') {
    window.showToast('Permintaan telah dibatalkan. Warna pesanan berubah menjadi Merah.');
  }
}
window.handleUserCancel = handleUserCancel;

/**
 * Helper untuk menentukan konfigurasi tampilan status pada Invoice PDF (teks, warna strip, badge, dan catatan).
 * Membedakan dengan jelas antara status 'Paid' (Sudah Bayar / Biru) dan 'Found!' / 'Approved' (Disetujui Admin / Hijau).
 * 
 * @param {string} rawStatus 
 * @returns {Object} Konfigurasi status invoice
 */
function getInvoiceStatusConfig(rawStatus) {
  const s = String(rawStatus || '').toLowerCase().trim();
  
  if (s.includes('paid') || s.includes('lanjut') || s.includes('bayar')) {
    return {
      statusKey: 'paid',
      tagText: 'PAID & VERIFIED',
      tagColor: [174, 215, 247],      // Light blue #aed7f7
      stripColor: [59, 130, 246],     // Blue #3b82f6
      statusBarText: 'STATUS: PAID / LUNAS (PEMBAYARAN DITERIMA & SELESAI)',
      orderStatusText: 'Lunas / Selesai (Paid)',
      filePrefix: 'PAID',
      boxBg: [239, 246, 255],         // Light blue #eff6ff
      boxBorder: [147, 197, 253],     // Blue border #93c5fd
      boxTitleColor: [30, 64, 175],   // Dark blue #1e40af
      boxTextColor: [29, 78, 216],    // Blue #1d4ed8
      boxTitle: 'BUKTI TRANSAKSI & PEMBAYARAN SAH (LUNAS):',
      boxLines: [
        '1. Pembayaran untuk pesanan merchandise ini telah selesai dan terverifikasi sah.',
        '2. Tim wastingmoney sedang memproses pengemasan dan persiapan pengiriman merchandise.',
        '3. Nomor resi pengiriman akan dikirimkan kepada pemohon melalui kontak yang terdaftar.',
        '4. Simpan berkas Invoice ini sebagai tanda bukti resmi transaksi kepemilikan Anda.'
      ],
      htmlBadgeClass: 'badge-paid',
      htmlNoticeClass: 'notice-paid'
    };
  } else if (s.includes('found') || s.includes('approve')) {
    return {
      statusKey: 'approved',
      tagText: 'APPROVED / FOUND',
      tagColor: [187, 248, 182],      // Light green #bbf8b6
      stripColor: [34, 197, 94],      // Green #22c55e
      statusBarText: 'STATUS: APPROVED / FOUND (DISETUJUI ADMIN)',
      orderStatusText: 'Disetujui Admin (Siap Bayar)',
      filePrefix: 'APPROVED',
      boxBg: [240, 253, 244],         // Light green #f0fdf4
      boxBorder: [187, 248, 182],     // Green border #bbf8b6
      boxTitleColor: [22, 101, 52],   // Dark green #166534
      boxTextColor: [21, 128, 61],    // Green #15803d
      boxTitle: 'PANDUAN KONFIRMASI KEPADA ADMIN (UNTUK USER):',
      boxLines: [
        '1. Unduh dan simpan berkas Invoice PDF ini.',
        '2. Hubungi Admin wastingmoney melalui WhatsApp atau email resmi: admin@wastingmoney.id',
        '3. Kirimkan berkas invoice ini kepada admin untuk mengabari bahwa Anda siap bertransaksi.',
        '4. Klik tombol "Lanjut" di keranjang belanja setelah pembayaran untuk mengubah status menjadi biru (Paid).'
      ],
      htmlBadgeClass: 'badge-approved',
      htmlNoticeClass: 'notice-approved'
    };
  } else if (s.includes('process') || s.includes('progress')) {
    return {
      statusKey: 'process',
      tagText: 'IN PROCESS',
      tagColor: [254, 240, 138],      // Light yellow #fef08a
      stripColor: [234, 179, 8],      // Yellow #eab308
      statusBarText: 'STATUS: IN PROCESS (SEDANG DIPROSES ADMIN)',
      orderStatusText: 'Sedang Diproses (Tunggu Approve)',
      filePrefix: 'PROCESS',
      boxBg: [254, 252, 232],         // Light yellow #fefce8
      boxBorder: [254, 240, 138],     // Yellow border #fef08a
      boxTitleColor: [133, 77, 14],   // Dark yellow #854d0e
      boxTextColor: [113, 63, 18],
      boxTitle: 'STATUS PESANAN: DALAM PROSES PENCARIAN & VERIFIKASI',
      boxLines: [
        '1. Tim admin sedang memverifikasi ketersediaan dan kondisi barang merchandise.',
        '2. Tunggu admin menyetujui request dan menginput harga penawaran resmi.',
        '3. Status kartu akan otomatis berubah menjadi hijau (Found!) setelah disetujui.'
      ],
      htmlBadgeClass: 'badge-process',
      htmlNoticeClass: 'notice-process'
    };
  } else if (s.includes('cancel') || s.includes('batal') || s.includes('reject')) {
    return {
      statusKey: 'cancel',
      tagText: 'CANCELLED',
      tagColor: [254, 202, 202],      // Light red #fca5a5
      stripColor: [239, 68, 68],      // Red #ef4444
      statusBarText: 'STATUS: CANCELLED (PERMINTAAN DIBATALKAN)',
      orderStatusText: 'Dibatalkan (Cancel)',
      filePrefix: 'CANCEL',
      boxBg: [254, 242, 242],         // Light red #fef2f2
      boxBorder: [254, 202, 202],     // Red border #fca5a5
      boxTitleColor: [153, 27, 27],   // Dark red #991b1b
      boxTextColor: [185, 28, 28],
      boxTitle: 'STATUS REQUEST: DIBATALKAN (CANCEL)',
      boxLines: [
        '1. Permintaan merchandise ini telah dibatalkan oleh pengguna atau admin.',
        '2. Tidak ada kewajiban transaksi atau pembayaran untuk pesanan yang dibatalkan.'
      ],
      htmlBadgeClass: 'badge-cancel',
      htmlNoticeClass: 'notice-cancel'
    };
  } else {
    return {
      statusKey: 'queue',
      tagText: 'QUEUE / REQUEST',
      tagColor: [203, 213, 225],      // Light slate #cbd5e1
      stripColor: [100, 116, 139],     // Slate #64748b
      statusBarText: 'STATUS: QUEUE (MENUNGGU REVIEW ADMIN)',
      orderStatusText: 'Menunggu Review Admin',
      filePrefix: 'QUEUE',
      boxBg: [248, 250, 252],         // Slate #f8fafc
      boxBorder: [226, 232, 240],     // Border #e2e8f0
      boxTitleColor: [71, 85, 105],   // Slate-700
      boxTextColor: [100, 116, 139],
      boxTitle: 'STATUS REQUEST: ANTREAN BARU (QUEUE)',
      boxLines: [
        '1. Request merchandise baru telah berhasil diajukan ke dalam antrean sistem.',
        '2. Admin wastingmoney akan segera memeriksa ketersediaan dan keaslian barang.',
        '3. Pantau status pemesanan secara berkala di halaman keranjang belanja.'
      ],
      htmlBadgeClass: 'badge-queue',
      htmlNoticeClass: 'notice-queue'
    };
  }
}
window.getInvoiceStatusConfig = getInvoiceStatusConfig;

/**
 * Menghasilkan dan mengunduh berkas Invoice resmi wastingmoney dalam format PDF
 * menggunakan pustaka jsPDF sehingga pengguna dapat mengabari admin untuk transaksi.
 * 
 * @param {number} itemId ID dari item request
 */
function exportInvoicePdf(itemId) {
  const item = currentRequests.find(r => r.id === itemId);
  if (!item) {
    if (typeof window.showToast === 'function') window.showToast('Data barang tidak ditemukan.');
    return;
  }

  const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const buyerName = item.fullName || (user ? (user.full_name || user.name || user.username) : 'Customer wastingmoney');
  const buyerEmail = user ? user.email : 'customer@wastingmoney.id';
  const buyerPhone = item.phoneNumber || (user ? user.phone_number : '0813 888 8888');
  const buyerAddress = item.address || (user ? user.address : 'Jl. Merch Hunter No. 26');
  const buyerPostal = item.postalCode || (user ? user.postal_code : '1945');
  const invoiceNumber = 'INV-' + (item.requestId ? item.requestId.replace('#', '') : String(item.id).padStart(6, '0'));
  const invoiceDate = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
  const statusConfig = getInvoiceStatusConfig(item.status);

  const jspdfLib = window.jspdf ? window.jspdf.jsPDF : (window.jsPDF || null);

  if (jspdfLib) {
    try {
      const doc = new jspdfLib({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });

      const pageWidth = doc.internal.pageSize.getWidth();
      const pageHeight = doc.internal.pageSize.getHeight();

      // 1. Header Banner Atas (Dark Blue / Slate #0f172a)
      doc.setFillColor(15, 23, 42);
      doc.rect(0, 0, pageWidth, 36, 'F');

      // Brand Title
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(22);
      doc.text('wastingmoney', 16, 18);

      // Subtitle
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor(203, 213, 225);
      doc.text('CURATED MERCHANDISE SOURCING & REQUEST PLATFORM', 16, 26);

      // Header Tag Kanan: OFFICIAL INVOICE (Disesuaikan dengan Status)
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(13);
      doc.setTextColor(statusConfig.tagColor[0], statusConfig.tagColor[1], statusConfig.tagColor[2]);
      doc.text(statusConfig.tagText, pageWidth - 16, 18, { align: 'right' });

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      doc.setTextColor(255, 255, 255);
      doc.text(invoiceNumber, pageWidth - 16, 26, { align: 'right' });

      // Strip Aksen Dinamis Sesuai Status
      doc.setFillColor(statusConfig.stripColor[0], statusConfig.stripColor[1], statusConfig.stripColor[2]);
      doc.rect(0, 36, pageWidth, 3, 'F');

      // 2. Bar Status Pesanan Dinamis
      let y = 48;
      doc.setTextColor(15, 23, 42);
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10.5);
      doc.text(statusConfig.statusBarText, 16, y);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      doc.setTextColor(100, 116, 139);
      doc.text(`Tanggal Cetak: ${invoiceDate}`, pageWidth - 16, y, { align: 'right' });

      y += 8;
      doc.setDrawColor(226, 232, 240);
      doc.setLineWidth(0.5);
      doc.line(16, y, pageWidth - 16, y);

      y += 8;

      // 3. Dua Kolom: Data Pembeli & Informasi Request
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(15, 23, 42);
      doc.text('DATA PEMOHON / PEMBELI:', 16, y);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor(51, 65, 85);
      y += 5;
      doc.text(`Nama Lengkap : ${buyerName}`, 16, y);
      y += 5;
      doc.text(`Email : ${buyerEmail}`, 16, y);
      y += 5;
      doc.text(`No. Handphone : ${buyerPhone}`, 16, y);
      y += 5;
      doc.text(`Alamat Pengiriman : ${buyerAddress}`, 16, y);
      y += 5;
      doc.text(`Kode Pos : ${buyerPostal}`, 16, y);

      let rightY = y - 20;
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(15, 23, 42);
      doc.text('RINCIAN PERMINTAAN:', pageWidth / 2 + 10, rightY);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor(51, 65, 85);
      rightY += 5;
      doc.text(`Request ID : ${item.requestId}`, pageWidth / 2 + 10, rightY);
      rightY += 5;
      doc.text(`Status Pesanan : ${statusConfig.orderStatusText}`, pageWidth / 2 + 10, rightY);
      rightY += 5;
      const statusDateLabel = statusConfig.statusKey === 'paid' ? 'Waktu Pembayaran' : 'Waktu Disetujui';
      doc.text(`${statusDateLabel} : ${item.statusDate || invoiceDate}`, pageWidth / 2 + 10, rightY);
      rightY += 5;
      doc.text(`Official Channel : wastingmoney Admin Desk`, pageWidth / 2 + 10, rightY);

      y = Math.max(y, rightY) + 10;

      // 4. Tabel Rincian Merchandise
      doc.setFillColor(241, 245, 249);
      doc.rect(16, y, pageWidth - 32, 9, 'F');
      doc.setDrawColor(203, 213, 225);
      doc.rect(16, y, pageWidth - 32, 9, 'S');

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(8.5);
      doc.setTextColor(15, 23, 42);
      doc.text('DESKRIPSI BARANG MERCHANDISE', 20, y + 6);
      doc.text('KATEGORI', 115, y + 6);
      doc.text('TAHUN', 145, y + 6);
      doc.text('TOTAL HARGA', pageWidth - 20, y + 6, { align: 'right' });

      y += 9;

      // Isi Tabel Barang
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(9.5);
      doc.setTextColor(15, 23, 42);
      y += 7;
      doc.text(`${item.artistBand}`, 20, y);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor(71, 85, 105);
      doc.text(`${item.merchType}`, 115, y);
      doc.text(`${item.releasedYear}`, 145, y);

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10.5);
      doc.setTextColor(77, 71, 195); // Purple accent #4d47c3
      doc.text(`${item.price && item.price !== '-' ? item.price : 'Rp 800.000'}`, pageWidth - 20, y, { align: 'right' });

      y += 5;
      doc.setFont('helvetica', 'italic');
      doc.setFontSize(8);
      doc.setTextColor(100, 116, 139);
      doc.text(`Edisi: ${item.merchEdition || '-'}`, 20, y);

      y += 5;
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(100, 116, 139);
      const splitDesc = doc.splitTextToSize(`Deskripsi: ${item.description || '-'}`, 150);
      doc.text(splitDesc, 20, y);
      y += (splitDesc.length * 4) + 6;

      doc.setDrawColor(226, 232, 240);
      doc.line(16, y, pageWidth - 16, y);

      y += 8;

      // 5. Kotak Catatan Penjual / Admin
      doc.setFillColor(254, 252, 232);
      doc.roundedRect(16, y, pageWidth - 32, 20, 3, 3, 'F');
      doc.setDrawColor(254, 240, 138);
      doc.roundedRect(16, y, pageWidth - 32, 20, 3, 3, 'S');

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(8.5);
      doc.setTextColor(133, 77, 14);
      doc.text('CATATAN DARI ADMIN / SELLER:', 20, y + 6);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor(113, 63, 18);
      const noteText = item.sellerNotes && item.sellerNotes !== '-' ? item.sellerNotes : 'Koleksi original terverifikasi, barang siap dikirim setelah pembayaran dilanjutkan.';
      const splitNotes = doc.splitTextToSize(noteText, pageWidth - 42);
      doc.text(splitNotes, 20, y + 12);

      y += 28;

      // 6. Kotak Panduan / Bukti Transaksi Sesuai Status (Approved vs Paid)
      doc.setFillColor(statusConfig.boxBg[0], statusConfig.boxBg[1], statusConfig.boxBg[2]);
      doc.roundedRect(16, y, pageWidth - 32, 38, 4, 4, 'F');
      doc.setDrawColor(statusConfig.boxBorder[0], statusConfig.boxBorder[1], statusConfig.boxBorder[2]);
      doc.roundedRect(16, y, pageWidth - 32, 38, 4, 4, 'S');

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(9.5);
      doc.setTextColor(statusConfig.boxTitleColor[0], statusConfig.boxTitleColor[1], statusConfig.boxTitleColor[2]);
      doc.text(statusConfig.boxTitle, 20, y + 7);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor(statusConfig.boxTextColor[0], statusConfig.boxTextColor[1], statusConfig.boxTextColor[2]);
      statusConfig.boxLines.forEach((line, idx) => {
        doc.text(line, 20, y + 14 + (idx * 6));
      });

      // 7. Footer
      doc.setFillColor(15, 23, 42);
      doc.rect(0, pageHeight - 16, pageWidth, 16, 'F');

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(8.5);
      doc.setTextColor(255, 255, 255);
      doc.text('wastingmoney • Curated Merchandise Hunter', 16, pageHeight - 7);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8);
      doc.setTextColor(203, 213, 225);
      doc.text('admin@wastingmoney.id • https://wastingmoney.id', pageWidth - 16, pageHeight - 7, { align: 'right' });

      // Simpan file dengan nama yang mencantumkan status
      const cleanReqId = (item.requestId || '000000').replace(/[^a-zA-Z0-9]/g, '');
      doc.save(`Invoice_${statusConfig.filePrefix}_wastingmoney_${cleanReqId}.pdf`);

      if (typeof window.showToast === 'function') {
        const toastMsg = statusConfig.statusKey === 'paid' 
          ? `Invoice Lunas (${invoiceNumber}) berhasil diunduh!`
          : `Invoice Approval (${invoiceNumber}) berhasil diunduh! Silakan kirimkan ke admin.`;
        window.showToast(toastMsg);
      }
      return;
    } catch(err) {
      console.error('jsPDF render error:', err);
    }
  }

  // Fallback print modal jika jsPDF gagal dimuat
  fallbackPrintInvoice(item, buyerName, buyerEmail, buyerPhone, buyerAddress, buyerPostal, invoiceNumber, invoiceDate);
}
window.exportInvoicePdf = exportInvoicePdf;

/**
 * Fallback browser printing dialog jika pustaka jsPDF belum selesai diunduh.
 */
function fallbackPrintInvoice(item, buyerName, buyerEmail, buyerPhone, buyerAddress, buyerPostal, invoiceNumber, invoiceDate) {
  const statusConfig = getInvoiceStatusConfig(item.status);
  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    if (typeof window.showToast === 'function') window.showToast('Izinkan pop-up untuk mengunduh invoice.');
    return;
  }

  const printHtml = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>${invoiceNumber} (${statusConfig.filePrefix}) - wastingmoney Official Invoice</title>
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #0f172a; margin: 0; padding: 28px; background: #fff; line-height: 1.5; }
        .invoice-card { max-width: 760px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
        .header { background: #0f172a; color: #fff; padding: 20px 24px; border-radius: 12px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid rgb(${statusConfig.stripColor.join(',')}); }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; color: #cbd5e1; font-size: 11px; }
        .badge { font-size: 12px; font-weight: 800; padding: 6px 14px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-approved { background: #dcfce7; color: #15803d; border: 1px solid #bbf8b6; }
        .badge-paid { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .badge-process { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .badge-cancel { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-queue { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; font-size: 13px; }
        .box h3 { margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; font-weight: 700; }
        .box p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px; }
        th { background: #f1f5f9; text-align: left; padding: 10px 14px; border: 1px solid #e2e8f0; font-size: 11px; text-transform: uppercase; color: #475569; }
        td { padding: 12px 14px; border: 1px solid #e2e8f0; }
        .price { font-size: 18px; font-weight: 800; color: #4d47c3; text-align: right; }
        .notice { padding: 18px; border-radius: 10px; font-size: 13px; margin-top: 16px; line-height: 1.6; }
        .notice-approved { background: #f0fdf4; border: 1.5px solid #bbf8b6; color: #166534; }
        .notice-paid { background: #eff6ff; border: 1.5px solid #93c5fd; color: #1e40af; }
        .notice-process { background: #fefce8; border: 1.5px solid #fef08a; color: #854d0e; }
        .notice-cancel { background: #fef2f2; border: 1.5px solid #fca5a5; color: #991b1b; }
        .notice-queue { background: #f8fafc; border: 1.5px solid #e2e8f0; color: #334155; }
        .footer { margin-top: 24px; text-align: center; font-size: 12px; color: #94a3b8; }
        @media print { body { padding: 0; } .invoice-card { border: none; box-shadow: none; padding: 0; } }
      </style>
    </head>
    <body>
      <div class="invoice-card">
        <div class="header">
          <div>
            <h1>wastingmoney</h1>
            <p>OFFICIAL MERCHANDISE REQUEST & SOURCING INVOICE</p>
          </div>
          <div class="badge ${statusConfig.htmlBadgeClass}">${statusConfig.tagText}</div>
        </div>
        <div class="grid">
          <div class="box">
            <h3>Data Pemohon / Pembeli</h3>
            <p><strong>Nama:</strong> ${buyerName}</p>
            <p><strong>Email:</strong> ${buyerEmail}</p>
            <p><strong>Telepon:</strong> ${buyerPhone}</p>
            <p><strong>Alamat:</strong> ${buyerAddress} (${buyerPostal})</p>
          </div>
          <div class="box">
            <h3>Detail Permintaan</h3>
            <p><strong>Invoice No:</strong> ${invoiceNumber}</p>
            <p><strong>Request ID:</strong> ${item.requestId}</p>
            <p><strong>Tanggal Cetak:</strong> ${invoiceDate}</p>
            <p><strong>Status Pesanan:</strong> ${statusConfig.orderStatusText}</p>
          </div>
        </div>
        <table>
          <thead>
            <tr>
              <th>Deskripsi Barang</th>
              <th>Kategori</th>
              <th>Tahun</th>
              <th>Edisi</th>
              <th style="text-align: right;">Total Harga</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>${item.artistBand}</strong><br><small style="color:#64748b">${item.description || '-'}</small></td>
              <td>${item.merchType}</td>
              <td>${item.releasedYear}</td>
              <td>${item.merchEdition || '-'}</td>
              <td class="price">${item.price && item.price !== '-' ? item.price : 'Rp 800.000'}</td>
            </tr>
          </tbody>
        </table>
        <div class="box">
          <h3>Catatan Seller / Admin</h3>
          <p>${item.sellerNotes && item.sellerNotes !== '-' ? item.sellerNotes : 'Barang telah diverifikasi kondisi original dan siap dikirimkan setelah pembayaran dikonfirmasi.'}</p>
        </div>
        <div class="notice ${statusConfig.htmlNoticeClass}">
          <strong>${statusConfig.boxTitle}</strong><br>
          ${statusConfig.boxLines.join('<br>')}
        </div>
        <div class="footer">
          wastingmoney ID • support@wastingmoney.id • https://wastingmoney.id
        </div>
      </div>
      <script>
        window.onload = function() {
          setTimeout(function() { window.print(); }, 400);
        };
      <\/script>
    </body>
    </html>
  `;
  printWindow.document.write(printHtml);
  printWindow.document.close();
}
window.fallbackPrintInvoice = fallbackPrintInvoice;

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
window.handleUserSearch = handleUserSearch;

/**
 * Membuka atau menutup menu popover filter status saat tombol corong filter diklik.
 */
function toggleFilterDropdown() {
  const menu = document.getElementById('filterDropdownMenu');
  const sortMenu = document.getElementById('sortDropdownMenu');
  if (sortMenu) sortMenu.classList.add('hidden');
  if (!menu) return;
  menu.classList.toggle('hidden');
}
window.toggleFilterDropdown = toggleFilterDropdown;

/**
 * Menutup popover filter status secara eksplisit.
 */
function closeFilterDropdown() {
  const menu = document.getElementById('filterDropdownMenu');
  if (menu) menu.classList.add('hidden');
}
window.closeFilterDropdown = closeFilterDropdown;

/**
 * Membuka atau menutup menu popover sort urutan saat tombol chevrons diklik.
 */
function toggleSortDropdown() {
  const menu = document.getElementById('sortDropdownMenu');
  const filterMenu = document.getElementById('filterDropdownMenu');
  if (filterMenu) filterMenu.classList.add('hidden');
  if (!menu) return;
  menu.classList.toggle('hidden');
}
window.toggleSortDropdown = toggleSortDropdown;

/**
 * Filter status langsung sesuai tombol Foto 1:
 * Queueing, In process, Found, Paid, Cancel
 * 
 * @param {string} status Nama status filter
 */
function selectStatusFilterDirect(status) {
  if (activeStatusFilter === status) {
    activeStatusFilter = 'all'; // toggle off jika diklik lagi
  } else {
    activeStatusFilter = status;
  }

  document.querySelectorAll('.user-filter-status-btn').forEach(btn => {
    btn.classList.remove('active-filter');
  });

  if (activeStatusFilter !== 'all') {
    const activeBtn = document.querySelector(`.user-filter-status-btn[onclick*="'${status}'"]`);
    if (activeBtn) activeBtn.classList.add('active-filter');
    if (typeof window.showToast === 'function') window.showToast(`Filter: ${status}`);
  } else {
    if (typeof window.showToast === 'function') window.showToast('Menampilkan semua request');
  }

  updateFilterBadge();
  renderCards();
  document.getElementById('filterDropdownMenu')?.classList.add('hidden');
}
window.selectStatusFilterDirect = selectStatusFilterDirect;

/**
 * Sort daftar kartu sesuai pilihan Foto 2:
 * Artist Name atau Request Date
 * 
 * @param {'artist'|'date'} criteria Kriteria pengurutan
 */
function selectSortOptionDirect(criteria) {
  if (currentSortBy === criteria) {
    sortOrderAsc = !sortOrderAsc;
  } else {
    currentSortBy = criteria;
    sortOrderAsc = true;
  }

  document.querySelectorAll('.user-sort-btn-option').forEach(b => b.classList.remove('active-sort'));

  if (criteria === 'artist') {
    document.getElementById('sortBtnArtist')?.classList.add('active-sort');
    currentRequests.sort((a, b) => {
      const nameA = (a.artistBand || a.title || '').toLowerCase();
      const nameB = (b.artistBand || b.title || '').toLowerCase();
      return sortOrderAsc ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
    });
    if (typeof window.showToast === 'function') window.showToast(`Urutkan: Artist Name (${sortOrderAsc ? 'A-Z' : 'Z-A'})`);
  } else if (criteria === 'date') {
    document.getElementById('sortBtnDate')?.classList.add('active-sort');
    currentRequests.sort((a, b) => {
      const idA = a.id || 0;
      const idB = b.id || 0;
      return sortOrderAsc ? idA - idB : idB - idA;
    });
    if (typeof window.showToast === 'function') window.showToast(`Urutkan: Request Date (${sortOrderAsc ? 'Lama ke Baru' : 'Terbaru'})`);
  }

  renderCards();
  document.getElementById('sortDropdownMenu')?.classList.add('hidden');
}
window.selectSortOptionDirect = selectSortOptionDirect;

/**
 * Menampilkan atau menyembunyikan titik indikator aktif pada tombol filter header.
 */
function updateFilterBadge() {
  const dot = document.getElementById('filterActiveDot');
  if (!dot) return;

  const isFiltered = (activeStatusFilter !== 'all');
  if (isFiltered) {
    dot.classList.remove('hidden');
  } else {
    dot.classList.add('hidden');
  }
}
window.updateFilterBadge = updateFilterBadge;

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
window.updateFilterCounts = updateFilterCounts;
