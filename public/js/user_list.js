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



/**
 * Membuka modal Lightbox Preview foto pada halaman User List
 */
function openUserPhotoLightbox(imgSrc, title) {
  if (!imgSrc) return;
  const modal = document.getElementById('userPhotoLightboxModal');
  const img = document.getElementById('userPhotoLightboxImg');
  const titleEl = document.getElementById('userPhotoLightboxTitle');
  if (img) img.src = imgSrc;
  if (titleEl && title) titleEl.textContent = title;
  if (modal) modal.classList.remove('hidden');
  if (window.lucide) lucide.createIcons();
}
window.openUserPhotoLightbox = openUserPhotoLightbox;

/**
 * Menutup modal Lightbox Preview foto pada halaman User List
 */
function closeUserPhotoLightbox() {
  const modal = document.getElementById('userPhotoLightboxModal');
  if (modal) modal.classList.add('hidden');
}
window.closeUserPhotoLightbox = closeUserPhotoLightbox;

/**
 * Data formulir request belanja merchandise dimuat secara dinamis dari database server
 * (/api/submissions/my) dan disinkronisasikan secara realtime dengan Firestore & LocalStorage.
 * Foto yang ditampilkan selalu mengikuti foto referensi yang diunggah pengguna (userImage)
 * atau foto temuan barang yang diunggah oleh pihak administrator (adminImages).
 */
let currentRequests = [];
let activeStatusFilter = 'all';
let activeTypeFilter = 'all';
let activeSearchQuery = '';
let selectedRequestItem = null;

// State Sorting:
// currentSortBy: 'default' | 'artist' | 'date'
// currentSortDir: 'asc' | 'desc'
// - Untuk 'artist': 'asc' (A ke Z) -> 'desc' (Z ke A) -> 'default' (Nonaktif)
// - Untuk 'date': 'desc' (Paling Baru) -> 'asc' (Paling Lama) -> 'default' (Nonaktif)
let currentSortBy = 'default';
let currentSortDir = 'desc';

/**
 * Inisialisasi halaman saat dokumen selesai dimuat.
 */
document.addEventListener('DOMContentLoaded', async () => {
  checkUserAuth();
  showUserListSkeleton(6);
  await loadLiveSubmissions();
  updateFilterCounts();
  startUserListLiveSync();

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
 * Menampilkan baris skeleton loading kartu shimmer pada katalog request merch user.
 */
function showUserListSkeleton(count = 6) {
  const grid = document.getElementById('userCardsGrid');
  if (!grid) return;
  const emptyState = document.getElementById('emptyCatalogState');
  if (emptyState) emptyState.classList.add('hidden');
  let html = '';
  for (let i = 0; i < count; i++) {
    html += `
      <div class="user-card-skeleton">
        <div class="skeleton-img-wrap">
          <div class="shimmer-wave"></div>
          <i data-lucide="image" class="w-8 h-8 text-slate-300"></i>
        </div>
        <div class="skeleton-info">
          <div class="skeleton-pill"><div class="shimmer-wave"></div></div>
          <div class="skeleton-title"><div class="shimmer-wave"></div></div>
          <div class="skeleton-sub"><div class="shimmer-wave"></div></div>
        </div>
      </div>
    `;
  }
  grid.innerHTML = html;
  if (window.lucide) lucide.createIcons();
}
window.showUserListSkeleton = showUserListSkeleton;

/**
 * Memuat request belanja milik user secara realtime dari database server (/api/submissions/my)
 * maupun localStorage, yang ter-skop KHUSUS hanya untuk akun user yang sedang aktif.
 */
async function loadLiveSubmissions() {
  try {
    const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
    const token = localStorage.getItem('wm_token') || localStorage.getItem('rakap_token');

    if (!user) {
      currentRequests = [];
      renderCards();
      updateFilterCounts();
      return;
    }

    let serverSubmissions = [];
    try {
      // Ambil riwayat submission milik user aktif ini saja
      const queryParams = `?user_id=${user.id || ''}&email=${encodeURIComponent(user.email || '')}`;
      const res = await fetch(`/api/submissions/my${queryParams}`, {
        headers: {
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        }
      });
      if (res.ok) {
        const json = await res.json();
        if (json && json.success && Array.isArray(json.data)) {
          serverSubmissions = json.data;
        }
      }
    } catch(err) {
      console.log('Server /api/submissions/my offline or network error', err);
    }

    // Ambil data request lokal khusus untuk akun yang sedang login (strict scoping)
    const allLocal = JSON.parse(localStorage.getItem('wm_user_created_requests') || '[]');
    const activeEmail = (user.email || '').trim().toLowerCase();
    const activeId = String(user.id || '');
    const localSubmissions = allLocal.filter(l => {
      if (l.user_email && activeEmail && l.user_email.trim().toLowerCase() === activeEmail) return true;
      if (l.user_id && activeId && String(l.user_id) === activeId) return true;
      return false;
    });

    let merged = [];

    // 1. Masukkan data server milik user
    serverSubmissions.forEach(sub => {
      const mapped = mapStatusToTheme(sub.status);
      const userPhoto = sub.userImage || sub.imageUrl || sub.image_url || '/assets/placeholder_merch.svg';
      const adminPhotos = Array.isArray(sub.adminImages) && sub.adminImages.length > 0 
        ? sub.adminImages 
        : (Array.isArray(sub.images) ? sub.images.filter(x => x && x !== userPhoto) : []);

      // Tampilkan foto temuan admin pada kartu jika admin sudah mengunggah foto
      const displayPhoto = (adminPhotos.length > 0) ? adminPhotos[0] : (sub.image || userPhoto);

      merged.push({
        ...sub,
        userImage: userPhoto,
        adminImages: adminPhotos,
        image: displayPhoto,
        displayImage: displayPhoto,
        images: adminPhotos,
        status: mapped.displayStatus,
        theme: mapped.theme,
        dotClass: mapped.dotClass
      });
    });

    // 2. Terapkan pembaruan terakhir admin dari localStorage HANYA jika memang milik user aktif ini
    try {
      const lastAdminEdit = JSON.parse(localStorage.getItem('wm_admin_last_updated') || 'null');
      if (lastAdminEdit) {
        const isForThisUser = 
          (lastAdminEdit.user_email && activeEmail && lastAdminEdit.user_email.trim().toLowerCase() === activeEmail) ||
          (lastAdminEdit.user_id && activeId && String(lastAdminEdit.user_id) === activeId);

        if (isForThisUser) {
          const adminNum = parseInt(String(lastAdminEdit.requestId || lastAdminEdit.id).replace(/[^0-9]/g, ''), 10);
          const target = merged.find(m => 
            String(m.id) === String(lastAdminEdit.id) || 
            parseInt(String(m.requestId).replace(/[^0-9]/g, ''), 10) === adminNum
          );
          if (target) {
            const mapped = mapStatusToTheme(lastAdminEdit.status);
            target.status = mapped.displayStatus;
            target.theme = mapped.theme;
            target.dotClass = mapped.dotClass;
            if (lastAdminEdit.price) target.price = lastAdminEdit.price;
            if (lastAdminEdit.sellerNotes) target.sellerNotes = lastAdminEdit.sellerNotes;
            if (lastAdminEdit.adminImages && Array.isArray(lastAdminEdit.adminImages)) {
              target.adminImages = lastAdminEdit.adminImages;
              target.images = lastAdminEdit.adminImages;
              if (lastAdminEdit.adminImages.length > 0) {
                target.image = lastAdminEdit.adminImages[0];
                target.displayImage = lastAdminEdit.adminImages[0];
              }
            } else if (lastAdminEdit.images && Array.isArray(lastAdminEdit.images)) {
              target.adminImages = lastAdminEdit.images.filter(x => x && x !== target.userImage);
              target.images = target.adminImages;
              if (target.adminImages.length > 0) {
                target.image = target.adminImages[0];
                target.displayImage = target.adminImages[0];
              }
            }
          }
        }
      }
    } catch(e) {}

    // 3. Tambahkan submission lokal user jika belum tersinkron ke server
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
          userImage: sub.userImage || sub.image || '/assets/placeholder_merch.svg',
          image: sub.image || sub.userImage || '/assets/placeholder_merch.svg',
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

// Auto-Sync Realtime Polling: Memperbarui data secara dinamis dari database server setiap 3.5 detik
let liveSyncInterval = null;
function startUserListLiveSync() {
  if (liveSyncInterval) clearInterval(liveSyncInterval);
  liveSyncInterval = setInterval(async () => {
    // Jangan jalankan saat tab browser tidak aktif / diminimalkan
    if (document.hidden) return;

    const prevSelectedId = selectedRequestItem ? selectedRequestItem.id : null;
    const prevSelectedStatus = selectedRequestItem ? selectedRequestItem.status : null;
    const prevSelectedPrice = selectedRequestItem ? selectedRequestItem.price : null;
    const prevSelectedAdminPhotos = selectedRequestItem && Array.isArray(selectedRequestItem.adminImages) 
      ? selectedRequestItem.adminImages.length 
      : 0;

    await loadLiveSubmissions();

    // Jika modal detail sedang dibuka, perbarui isinya secara dinamis jika ada perubahan data dari admin
    if (prevSelectedId) {
      const updated = currentRequests.find(r => r.id === prevSelectedId);
      if (updated) {
        const hasChanged = 
          updated.status !== prevSelectedStatus || 
          updated.price !== prevSelectedPrice || 
          (Array.isArray(updated.adminImages) ? updated.adminImages.length : 0) !== prevSelectedAdminPhotos;
        
        if (hasChanged) {
          openUserModal(updated.id);
        }
      }
    }
  }, 3500);
}
window.startUserListLiveSync = startUserListLiveSync;

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

  // Terapkan pengurutan (Sorting) aktif sesuai preferensi pengguna
  const sorted = applySortToItems(filtered);

  // Perbarui teks ringkasan di dalam popover filter
  const summaryText = document.getElementById('filterSummaryText');
  if (summaryText) {
    summaryText.textContent = `Menampilkan ${sorted.length} dari ${currentRequests.length} item`;
  }

  if (sorted.length === 0) {
    grid.innerHTML = '';
    if (emptyState) {
      emptyState.classList.remove('hidden');
      const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
      if (!user) {
        emptyState.innerHTML = `
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-50 flex items-center justify-center text-[#4e46e5]">
            <i data-lucide="log-in" class="w-8 h-8"></i>
          </div>
          <h3 class="text-base font-bold text-slate-800">Silakan Masuk Terlebih Dahulu</h3>
          <p class="text-xs text-slate-500 mt-1 mb-4">Masuk untuk melihat Merch Request List milik akun Anda.</p>
          <a href="/auth" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#4e46e5] text-white text-xs font-bold shadow-md hover:bg-[#4338ca] transition-all">
            <i data-lucide="log-in" class="w-4 h-4"></i>
            <span>Masuk / Login</span>
          </a>
        `;
      } else {
        emptyState.innerHTML = `
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
            <i data-lucide="inbox" class="w-8 h-8"></i>
          </div>
          <h3 class="text-base font-bold text-slate-800">Belum Ada Request Merchandise</h3>
          <p class="text-xs text-slate-500 mt-1 mb-4">Akun Anda (${user.username || user.email}) belum memiliki riwayat request barang.</p>
          <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#4e46e5] text-white text-xs font-bold shadow-md hover:bg-[#4338ca] transition-all">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Buat Request Baru</span>
          </a>
        `;
      }
      if (window.lucide) lucide.createIcons();
    }
    return;
  }

  if (emptyState) emptyState.classList.add('hidden');
  grid.innerHTML = sorted.map(item => `
    <div 
      class="user-card-item ${item.theme}" 
      onclick="openUserModal(${item.id})"
      title="Klik untuk melihat detail & status request"
    >
      <!-- Product Image di dalam kontainer putih rounded -->
      <div class="user-card-img-wrap">
        <img 
          src="${item.image || '/assets/placeholder_merch.svg'}" 
          alt="${item.artistBand} ${item.merchType}" 
          class="user-card-img" 
          onerror="this.onerror=null; this.src='/assets/placeholder_merch.svg';"
          loading="lazy"
        />
      </div>

      <!-- Detail di bagian bawah kartu: Tahun & Nama Barang -->
      <div class="user-card-info">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.25rem;">
          <span class="user-card-year">${item.releasedYear}</span>
          ${item.status === 'Found!' ? `
            <a 
              href="${buildWhatsAppPayUrl(item)}" 
              class="user-card-wa-chip" 
              onclick="event.stopPropagation(); confirmPaymentWhatsApp(${item.id}, event);" 
              title="Bayar via WhatsApp (+62 813-8356-2602)"
            >
              <svg style="width: 10px; height: 10px; fill: currentColor; display: inline-block; margin-right: 3px;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>Bayar (WA)
            </a>
          ` : ''}
        </div>
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
 * Menghasilkan string waktu saat ini dalam format lokal DD/MM/YYYY HH:mm:ss.
 */
function getNowFormatted() {
  const now = new Date();
  const day = String(now.getDate()).padStart(2, '0');
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const year = now.getFullYear();
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
}
window.getNowFormatted = getNowFormatted;

/**
 * Memformat tanggal submission agar selalu sinkron dengan zona waktu lokal perangkat pengguna.
 * 
 * @param {Object} item Data submission
 * @returns {string} Tanggal terformat (DD/MM/YYYY HH:mm:ss)
 */
function formatSubmissionDate(item) {
  if (!item) return '';

  // 1. Coba parse dari created_at / createdAt ISO string ke waktu lokal browser
  const rawDate = item.created_at || item.createdAt;
  if (rawDate) {
    const d = new Date(rawDate);
    if (!isNaN(d.getTime())) {
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      const seconds = String(d.getSeconds()).padStart(2, '0');
      return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }
  }

  // 2. Jika ada statusDate dari backend / state
  if (item.statusDate && typeof item.statusDate === 'string' && item.statusDate.trim() !== '') {
    return item.statusDate.trim();
  }

  return '';
}
window.formatSubmissionDate = formatSubmissionDate;

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
  const item = currentRequests.find(r => String(r.id) === String(itemId) || String(r.requestId).replace(/[^0-9]/g, '') === String(itemId).replace(/[^0-9]/g, ''));
  if (!item) return;

  selectedRequestItem = item;

  const overlay = document.getElementById('userItemModalOverlay');
  const card = document.getElementById('userModalCard');

  // 1. Terapkan warna tema dinamis ke modal card
  card.className = `user-modal-card ${item.theme}`;

  // 2. Tentukan foto temuan admin dan foto user
  const userPhoto = item.userImage || item.image_url || '/assets/placeholder_merch.svg';
  let adminPhotosList = [];
  if (Array.isArray(item.adminImages) && item.adminImages.length > 0) {
    adminPhotosList = [...item.adminImages];
  } else if (Array.isArray(item.admin_images) && item.admin_images.length > 0) {
    adminPhotosList = [...item.admin_images];
  } else if (Array.isArray(item.images) && item.images.length > 0) {
    adminPhotosList = item.images.filter(img => img && img !== userPhoto);
  }

  // Jika admin sudah menemukan barang dan mengunggah foto, jadikan foto temuan admin sebagai preview utama
  const hasAdminPhotos = (adminPhotosList.length > 0);
  const initialMainPhoto = hasAdminPhotos ? adminPhotosList[0] : userPhoto;

  const mainImg = document.getElementById('userModalMainImg');
  if (mainImg) {
    mainImg.src = initialMainPhoto;
    mainImg.onerror = function() { this.src = '/assets/placeholder_merch.svg'; };
  }

  // 2a. Baris Foto 1: Foto dari Anda (User)
  const userThumbsRow = document.getElementById('userModalUserThumbsRow');
  if (userThumbsRow) {
    userThumbsRow.innerHTML = `
      <div 
        class="user-modal-thumb-box ${!hasAdminPhotos ? 'selected' : ''}" 
        onclick="switchUserModalPreview(this, '${escapeHtml(userPhoto)}', 'Foto Pengajuan Anda')"
        title="Foto dari Anda (Klik untuk melihat)"
      >
        <img src="${escapeHtml(userPhoto)}" alt="Foto Anda" onerror="this.src='/assets/placeholder_merch.svg'" />
      </div>
    `;
  }

  // 2b. Baris Foto 2: Foto dari Admin / Temuan Barang (jika ada)
  const adminContainer = document.getElementById('userModalAdminPhotosContainer');
  const thumbsRow = document.getElementById('userModalThumbsRow');
  const countTag = document.getElementById('userModalAdminPhotoCount');

  if (hasAdminPhotos) {
    if (adminContainer) adminContainer.classList.remove('hidden');
    if (countTag) countTag.textContent = `(${adminPhotosList.length} Foto Ditemukan)`;
    if (thumbsRow) {
      thumbsRow.innerHTML = adminPhotosList.map((imgSrc, idx) => `
        <div 
          class="user-modal-thumb-box ${idx === 0 ? 'selected' : ''}" 
          onclick="switchUserModalPreview(this, '${escapeHtml(imgSrc)}', 'Foto Temuan Admin ${idx + 1}')"
          title="Foto dari Admin ${idx + 1} (Klik untuk melihat)"
        >
          <img src="${escapeHtml(imgSrc)}" alt="Foto Admin ${idx + 1}" onerror="this.src='/assets/taylor_swift_shirt.png'" />
        </div>
      `).join('');
    }
  } else {
    if (adminContainer) adminContainer.classList.add('hidden');
    if (thumbsRow) thumbsRow.innerHTML = '';
  }

  // 3. Tampilkan data Request ID dan Status Badge
  const reqIdEl = document.getElementById('userModalReqId');
  if (reqIdEl) reqIdEl.textContent = item.requestId;
  
  const dotEl = document.getElementById('userModalStatusDot');
  if (dotEl) dotEl.className = `user-modal-status-dot ${item.dotClass}`;
  
  const statusTextEl = document.getElementById('userModalStatusText');
  const dateFormatted = formatSubmissionDate(item);
  if (statusTextEl) {
    statusTextEl.textContent = dateFormatted ? `${item.status} ${dateFormatted}` : `${item.status}`;
  }

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
      // Status Hijau (Admin sudah Approve / Menemukan Barang):
      // User melakukan pembayaran via WhatsApp Admin (+62 813-8356-2602). Status pesanan ke 'Paid' akan diubah langsung oleh Admin setelah verifikasi pembayaran.
      actionsArea.innerHTML = `
        <div class="user-status-notice found w-full mb-1">
          <i data-lucide="info" class="w-4 h-4"></i>
          <span>Barang Ditemukan! Silakan klik tombol <b>Bayar (Konfirmasi WA)</b> untuk konfirmasi ke WhatsApp Admin (+62 813-8356-2602).</span>
        </div>
        <a 
          href="${buildWhatsAppPayUrl(item)}" 
          class="user-btn-whatsapp-pay" 
          onclick="confirmPaymentWhatsApp(${item.id}, event)"
          title="Konfirmasi Pembayaran via WhatsApp ke +62 813-8356-2602"
        >
          <svg class="w-4 h-4 fill-current inline-block" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
          </svg>
          <span>Bayar (Konfirmasi WA)</span>
        </a>
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
          class="user-btn-cancel" 
          onclick="handleUserCancel(${item.id}, this)"
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
          onclick="handleUserCancel(${item.id}, this)"
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
          onclick="handleUserCancel(${item.id}, this)"
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

  // Aktifkan efek dynamic cursor zoom pada preview gambar utama
  setupUserModalDynamicZoom();

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
  if (mainImg) {
    mainImg.src = src;
    mainImg.style.transform = 'scale(1)';
    mainImg.style.transformOrigin = 'center center';
  }

  const thumbs = document.querySelectorAll('.user-modal-thumb-box');
  thumbs.forEach(t => t.classList.remove('selected'));
  if (el) el.classList.add('selected');
}
window.switchModalThumb = switchModalThumb;

/**
 * Mengganti tampilan gambar pada PREVIEW TUNGGAL di modal detail user ketika salah satu thumbnail (User atau Admin) diklik.
 * 
 * @param {HTMLElement} el Elemen thumbnail yang diklik
 * @param {string} src URL sumber gambar
 * @param {string} [title] Judul foto
 */
function switchUserModalPreview(el, src, title) {
  const mainImg = document.getElementById('userModalMainImg');
  if (mainImg) {
    mainImg.src = src;
    mainImg.style.transform = 'scale(1)';
    mainImg.style.transformOrigin = 'center center';
  }

  const thumbs = document.querySelectorAll('#userModalUserThumbsRow .user-modal-thumb-box, #userModalThumbsRow .user-modal-thumb-box');
  thumbs.forEach(t => t.classList.remove('selected'));
  if (el) el.classList.add('selected');
}
window.switchUserModalPreview = switchUserModalPreview;
window.switchUserModalAdminThumb = switchUserModalPreview; // Backward compatibility

/**
 * Inisialisasi efek Dynamic Cursor Zoom pada preview gambar utama modal user list.
 * Saat kursor mouse digerakkan di atas gambar, gambar akan membesar (zoom in 2.2x)
 * dan titik fokus pembesaran (transform-origin) mengikuti posisi kursor secara dinamis 60fps.
 */
function setupUserModalDynamicZoom() {
  const wrap = document.querySelector('.user-modal-main-img-wrap');
  const img = document.getElementById('userModalMainImg');
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

    // Pergerakan instan tanpa lag saat kursor berpindah
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
window.setupUserModalDynamicZoom = setupUserModalDynamicZoom;

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
  item.statusDate = getNowFormatted();

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
 * Menghasilkan URL WhatsApp untuk pembayaran pesanan.
 * 
 * @param {object} item Data request merchandise
 * @returns {string} URL WhatsApp
 */
function buildWhatsAppPayUrl(item) {
  if (!item) return '#';
  const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const customerName = user ? (user.username || user.name || user.email) : 'Customer';
  const customerEmail = user ? (user.email || '') : '';
  const customerPhone = user ? (user.phone || '') : '';
  const adminPhone = '6281383562602';

  let msg = `Halo Admin Wasting Money,\n\n`;
  msg += `Saya ingin melakukan konfirmasi pembayaran untuk pesanan merchandise berikut:\n\n`;
  msg += `*Detail Pesanan:*\n`;
  msg += `• *ID Request:* ${item.requestId || '#' + item.id}\n`;
  msg += `• *Merchandise:* ${item.artistBand} - ${item.merchType}\n`;
  if (item.merchEdition) msg += `• *Edisi:* ${item.merchEdition}\n`;
  if (item.releasedYear) msg += `• *Tahun:* ${item.releasedYear}\n`;
  msg += `• *Total Harga:* ${item.price || 'Sesuai kesepakatan'}\n`;
  if (item.sellerNotes) msg += `• *Catatan Admin:* ${item.sellerNotes}\n`;
  msg += `\n*Data Pemesan:*\n`;
  msg += `• *Nama:* ${customerName}\n`;
  if (customerEmail) msg += `• *Email:* ${customerEmail}\n`;
  if (customerPhone) msg += `• *No. HP:* ${customerPhone}\n`;
  msg += `\nMohon instruksi dan nomor rekening untuk proses transfer pembayaran. Terima kasih!`;

  return `https://api.whatsapp.com/send?phone=${adminPhone}&text=${encodeURIComponent(msg)}`;
}
window.buildWhatsAppPayUrl = buildWhatsAppPayUrl;

/**
 * Membuka WhatsApp ke Admin (+62 813-8356-2602) secara instan (auto direct)
 * tanpa terhalang pop-up blocker dan langsung membuka aplikasi WhatsApp (mobile) atau WhatsApp Web/Desktop.
 * 
 * @param {number} itemId ID dari item request yang akan dibayar
 * @param {Event} [event] Event klik opsional
 */
function confirmPaymentWhatsApp(itemId, event) {
  const item = currentRequests.find(r => 
    String(r.id) === String(itemId) || 
    String(r.requestId).replace(/[^0-9]/g, '') === String(itemId).replace(/[^0-9]/g, '')
  );
  if (!item) return;

  const adminPhone = '6281383562602';
  const user = (typeof window.getActiveUser === 'function') ? window.getActiveUser() : null;
  const customerName = user ? (user.username || user.name || user.email) : 'Customer';
  const customerEmail = user ? (user.email || '') : '';
  const customerPhone = user ? (user.phone || '') : '';

  let msg = `Halo Admin Wasting Money,\n\n`;
  msg += `Saya ingin melakukan konfirmasi pembayaran untuk pesanan merchandise berikut:\n\n`;
  msg += `*Detail Pesanan:*\n`;
  msg += `• *ID Request:* ${item.requestId || '#' + item.id}\n`;
  msg += `• *Merchandise:* ${item.artistBand} - ${item.merchType}\n`;
  if (item.merchEdition) msg += `• *Edisi:* ${item.merchEdition}\n`;
  if (item.releasedYear) msg += `• *Tahun:* ${item.releasedYear}\n`;
  msg += `• *Total Harga:* ${item.price || 'Sesuai kesepakatan'}\n`;
  if (item.sellerNotes) msg += `• *Catatan Admin:* ${item.sellerNotes}\n`;
  msg += `\n*Data Pemesan:*\n`;
  msg += `• *Nama:* ${customerName}\n`;
  if (customerEmail) msg += `• *Email:* ${customerEmail}\n`;
  if (customerPhone) msg += `• *No. HP:* ${customerPhone}\n`;
  msg += `\nMohon instruksi dan nomor rekening untuk proses transfer pembayaran. Terima kasih!`;

  const encodedMsg = encodeURIComponent(msg);
  const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  
  const nativeAppUrl = `whatsapp://send?phone=${adminPhone}&text=${encodedMsg}`;
  const webApiUrl = `https://api.whatsapp.com/send?phone=${adminPhone}&text=${encodedMsg}`;

  if (isMobile) {
    // Pada HP/Mobile: langsung auto direct ke aplikasi WhatsApp
    if (event) event.preventDefault();
    window.location.href = nativeAppUrl;
    setTimeout(() => {
      window.location.href = webApiUrl;
    }, 1500);
  } else {
    // Pada Desktop: langsung direct ke WhatsApp
    if (event) event.preventDefault();
    window.location.href = webApiUrl;
  }
}
window.confirmPaymentWhatsApp = confirmPaymentWhatsApp;

let isCanceling = false;

/**
 * Menangani aksi saat user memilih "Cancel" pada item.
 * Hanya bisa dieksekusi 1 kali dan menampilkan animasi loading spinner agar tidak diklik berulang kali.
 * Mengubah status item menjadi "Cancel" dan mengubah warna kartu menjadi MERAH secara otomatis.
 * 
 * @param {number} itemId ID dari item request yang dibatalkan
 * @param {HTMLElement} btnEl Elemen tombol cancel yang diklik
 */
async function handleUserCancel(itemId, btnEl) {
  if (isCanceling) return; // Mencegah klik ganda saat proses berjalan

  const item = currentRequests.find(r => r.id === itemId);
  if (!item || item.status === 'Cancel') return; // Hanya bisa 1 kali saja

  isCanceling = true;

  // Tampilkan animasi loading dan disable tombol segera
  const btn = btnEl || document.querySelector('.user-btn-cancel');
  if (btn) {
    btn.disabled = true;
    btn.classList.add('loading');
    btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin inline-block mr-1.5"></i><span>Membatalkan...</span>`;
    if (window.lucide) lucide.createIcons();
  }

  // Nonaktifkan tombol lain di modal selama pembatalan berlangsung
  const allModalBtns = document.querySelectorAll('#userModalActionsArea button');
  allModalBtns.forEach(b => {
    b.disabled = true;
  });

  item.status = 'Cancel';
  item.theme = 'theme-pink';
  item.dotClass = 'dot-pink';
  item.statusDate = getNowFormatted();

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
    console.log('Saved to local session', e);
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

  isCanceling = false;

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
        '2. Hubungi Admin wastingmoney melalui WhatsApp: +62 813-8356-2602 atau email resmi: admin@wastingmoney.id',
        '3. Kirimkan berkas invoice ini kepada admin untuk mengabari bahwa Anda siap bertransaksi.',
        '4. Admin akan memverifikasi pembayaran dan memperbarui status pesanan menjadi Lunas (Paid).'
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
 * Mengambil nilai timestamp kronologis untuk perbandingan waktu request.
 * Mendukung format ISO timestamp (created_at / createdAt), format tanggal statusDate, atau nomor urut ID.
 * 
 * @param {Object} item Data submission
 * @returns {number} Timestamp milidetik
 */
function getItemTimestamp(item) {
  if (!item) return 0;
  if (item.created_at) {
    const t = new Date(item.created_at).getTime();
    if (!isNaN(t) && t > 0) return t;
  }
  if (item.createdAt) {
    const t = new Date(item.createdAt).getTime();
    if (!isNaN(t) && t > 0) return t;
  }
  if (item.statusDate && typeof item.statusDate === 'string') {
    const parts = item.statusDate.trim().split(' ');
    if (parts.length >= 2) {
      const dParts = parts[0].split('/');
      const tSep = parts[1].includes(':') ? ':' : '.';
      const tParts = parts[1].split(tSep);
      if (dParts.length === 3) {
        const d = new Date(
          parseInt(dParts[2], 10),
          parseInt(dParts[1], 10) - 1,
          parseInt(dParts[0], 10),
          parseInt(tParts[0], 10) || 0,
          parseInt(tParts[1], 10) || 0,
          parseInt(tParts[2], 10) || 0
        );
        if (!isNaN(d.getTime())) return d.getTime();
      }
    }
  }
  return parseInt(String(item.id || 0).replace(/[^0-9]/g, ''), 10) || 0;
}
window.getItemTimestamp = getItemTimestamp;

/**
 * Menerapkan pengurutan (Sorting) aktif ke dalam array kartu merchandise.
 * Memastikan hasil sort konsisten dan tidak kembali ke default saat background polling berjalan.
 * 
 * @param {Array} items 
 * @returns {Array} Array yang sudah terurut
 */
function applySortToItems(items) {
  if (!Array.isArray(items)) return [];
  const list = [...items];

  if (currentSortBy === 'artist') {
    list.sort((a, b) => {
      const nameA = String(a.artistBand || a.title || '').trim().toLowerCase();
      const nameB = String(b.artistBand || b.title || '').trim().toLowerCase();
      return currentSortDir === 'asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
    });
  } else if (currentSortBy === 'date') {
    list.sort((a, b) => {
      const timeA = getItemTimestamp(a);
      const timeB = getItemTimestamp(b);
      // 'desc' = Paling Baru (Terbaru), 'asc' = Paling Lama (Terlama)
      return currentSortDir === 'desc' ? (timeB - timeA) : (timeA - timeB);
    });
  }

  return list;
}
window.applySortToItems = applySortToItems;

/**
 * Memperbarui tampilan warna dan ikon aktif pada tombol panah Up/Down di dropdown sort
 * serta indikator dual-panah di tombol header.
 */
function updateSortUI() {
  const rowArtist = document.getElementById('sortRowArtist');
  const rowDate = document.getElementById('sortRowDate');
  const arrowArtistUp = document.getElementById('sortArrowArtistUp');
  const arrowArtistDown = document.getElementById('sortArrowArtistDown');
  const arrowDateUp = document.getElementById('sortArrowDateUp');
  const arrowDateDown = document.getElementById('sortArrowDateDown');
  const headerArrows = document.getElementById('headerSortArrows');
  const sortDot = document.getElementById('sortActiveDot');

  // Reset semua elemen ke state netral
  if (rowArtist) rowArtist.classList.remove('active-row');
  if (rowDate) rowDate.classList.remove('active-row');
  if (arrowArtistUp) arrowArtistUp.classList.remove('active-arrow');
  if (arrowArtistDown) arrowArtistDown.classList.remove('active-arrow');
  if (arrowDateUp) arrowDateUp.classList.remove('active-arrow');
  if (arrowDateDown) arrowDateDown.classList.remove('active-arrow');
  if (headerArrows) {
    headerArrows.classList.remove('sort-up-active', 'sort-down-active');
  }
  if (sortDot) sortDot.classList.add('hidden');

  if (currentSortBy === 'artist') {
    if (rowArtist) rowArtist.classList.add('active-row');
    if (sortDot) sortDot.classList.remove('hidden');

    if (currentSortDir === 'asc') {
      // Urutan dari atas: A ke Z (Arrow UP aktif dengan warna kontras)
      if (arrowArtistUp) arrowArtistUp.classList.add('active-arrow');
      if (headerArrows) headerArrows.classList.add('sort-up-active');
    } else {
      // Urutan dari bawah: Z ke A (Arrow DOWN aktif dengan warna kontras)
      if (arrowArtistDown) arrowArtistDown.classList.add('active-arrow');
      if (headerArrows) headerArrows.classList.add('sort-down-active');
    }
  } else if (currentSortBy === 'date') {
    if (rowDate) rowDate.classList.add('active-row');
    if (sortDot) sortDot.classList.remove('hidden');

    if (currentSortDir === 'desc') {
      // Urutan dari yang terbaru: Paling Baru (Arrow UP aktif dengan warna kontras)
      if (arrowDateUp) arrowDateUp.classList.add('active-arrow');
      if (headerArrows) headerArrows.classList.add('sort-up-active');
    } else {
      // Urutan dari yang paling lama: Paling Lama (Arrow DOWN aktif dengan warna kontras)
      if (arrowDateDown) arrowDateDown.classList.add('active-arrow');
      if (headerArrows) headerArrows.classList.add('sort-down-active');
    }
  }
}
window.updateSortUI = updateSortUI;

/**
 * Memilih secara tepat kriteria dan arah pengurutan saat tombol panah Up atau Down diklik.
 * Jika panah yang sedang aktif diklik kembali, maka pengurutan dinonaktifkan (kembali ke default).
 * 
 * @param {'artist'|'date'} criteria Kriteria pengurutan
 * @param {'asc'|'desc'} direction Arah: 'asc' (A-Z atau Terlama), 'desc' (Z-A atau Terbaru)
 */
function setSortExact(criteria, direction) {
  // Jika panah yang sama diklik lagi saat sudah aktif -> Nonaktifkan / Reset
  if (currentSortBy === criteria && currentSortDir === direction) {
    currentSortBy = 'default';
    if (typeof window.showToast === 'function') {
      window.showToast('Urutan dinonaktifkan (Default)');
    }
  } else {
    currentSortBy = criteria;
    currentSortDir = direction;

    if (typeof window.showToast === 'function') {
      if (criteria === 'artist') {
        const text = direction === 'asc' ? 'A ke Z (Dari Atas)' : 'Z ke A (Dari Bawah)';
        window.showToast(`Urutkan Artist: ${text}`);
      } else if (criteria === 'date') {
        const text = direction === 'desc' ? 'Paling Baru (Terbaru)' : 'Paling Lama (Terlama)';
        window.showToast(`Urutkan Tanggal: ${text}`);
      }
    }
  }

  updateSortUI();
  renderCards();
}
window.setSortExact = setSortExact;

/**
 * Bergantian (toggle) arah pengurutan saat teks label baris diklik:
 * Up -> Down -> Nonaktif
 * 
 * @param {'artist'|'date'} criteria Kriteria pengurutan
 */
function toggleSortCriteria(criteria) {
  if (criteria === 'artist') {
    if (currentSortBy !== 'artist') {
      setSortExact('artist', 'asc');
    } else if (currentSortDir === 'asc') {
      setSortExact('artist', 'desc');
    } else {
      setSortExact('artist', 'desc'); // Akan menonaktifkan karena sama
    }
  } else if (criteria === 'date') {
    if (currentSortBy !== 'date') {
      setSortExact('date', 'desc');
    } else if (currentSortDir === 'desc') {
      setSortExact('date', 'asc');
    } else {
      setSortExact('date', 'asc'); // Akan menonaktifkan karena sama
    }
  }
}
window.toggleSortCriteria = toggleSortCriteria;

// Alias untuk kompatibilitas
window.selectSortOptionDirect = toggleSortCriteria;

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
