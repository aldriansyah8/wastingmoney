@extends('layouts.app')

@section('title', 'wastingmoney - Admin')

@section('styles')
  <!-- Dedicated CSS for Admin Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/admin.css">
@endsection

@section('content')
<div class="admin-page-container selection:bg-indigo-100 selection:text-indigo-800">
  
  <!-- ================= TOP HEADER (Matching admin_page1.png) ================= -->
  <header class="admin-header">
    <!-- Brand Logo -->
    <a href="/admin" class="flex items-center select-none cursor-pointer">
      <img src="/assets/logo.png" alt="wastingmoney" class="admin-logo-img" />
    </a>

    <!-- Right Icons: Cart & Profile -->
    <div class="flex items-center gap-4 text-slate-900 relative">
      <button 
        type="button" 
        onclick="showToast('Keranjang belanja kosong.')"
        class="p-1 hover:text-[#4e46e5] transition-colors cursor-pointer"
        title="Cart"
      >
        <i data-lucide="shopping-cart" class="w-5 sm:w-6 h-5 sm:h-6"></i>
      </button>

      <span class="text-slate-300 font-light select-none">|</span>

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

        <div id="adminDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50">
          <div class="px-4 py-2 border-b border-slate-100">
            <p class="text-xs text-slate-400 font-medium">Administrator:</p>
            <p id="adminDropdownName" class="text-sm font-bold text-slate-900 truncate">System Administrator</p>
            <p id="adminDropdownEmail" class="text-xs text-slate-500 truncate">admin@rakapteman.id</p>
          </div>
          <button
            type="button"
            onclick="handleAdminLogout()"
            class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors cursor-pointer"
          >
            <i data-lucide="log-out" class="w-4 h-4"></i>
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- ================= MAIN CONTENT: REQUESTS TABLE (Matching admin_page1.png) ================= -->
  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 py-6 sm:py-10">
    
    <!-- Table Container -->
    <div class="admin-table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="py-4 px-3">
              <div class="th-content">
                No <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Request ID <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Username <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Artist/Band <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Merch Type <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Released Year <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Status <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-4">
              <div class="th-content">
                Request Date <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-700"></i>
              </div>
            </th>
            <th class="py-4 px-3 text-center"></th>
          </tr>
        </thead>

        <tbody id="adminTableBody">
          <tr>
            <td colspan="9" class="py-12 text-center text-slate-400">
              <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto text-[#4e46e5] mb-2"></i>
              Memuat data pengajuan...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>

  <!-- ================= DETAIL & APPROVAL REVIEW MODAL (Matching Client Mockup Image 2) ================= -->
  <div id="adminReviewModal" class="hidden admin-modal-backdrop">
    <div class="admin-modal-card">
      <button
        type="button"
        onclick="closeAdminModal()"
        class="admin-modal-close-btn"
        title="Tutup Modal"
      >
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>

      <div class="admin-modal-layout">
        <!-- LEFT COLUMN: Product Gallery (Matching Mockup Image 2) -->
        <div class="admin-modal-gallery">
          <div class="admin-modal-main-img-wrap">
            <img id="modalPreviewImg" src="/assets/poster_art.png" alt="Merchandise Preview" class="admin-modal-main-img" />
          </div>

          <!-- Thumbnails Row -->
          <div class="admin-modal-thumbs-row">
            <div class="admin-thumb-box" onclick="selectThumbImage(this)">
              <img id="thumb1" src="/assets/poster_art.png" alt="Thumb 1" />
            </div>
            <div class="admin-thumb-box" onclick="selectThumbImage(this)">
              <img id="thumb2" src="/assets/poster_art.png" alt="Thumb 2" />
            </div>
            <div class="admin-thumb-box" onclick="selectThumbImage(this)">
              <img id="thumb3" src="/assets/poster_art.png" alt="Thumb 3" />
            </div>
            <div class="admin-thumb-upload-box" title="Maksimal 3 Foto">
              <i data-lucide="upload" class="w-3.5 h-3.5 text-white"></i>
              <span>Max 3 Photo</span>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Details & Action Form (Matching Mockup Image 2) -->
        <div class="admin-modal-info">
          <!-- Top Row: Request ID & Found Date -->
          <div class="admin-info-header-row">
            <div>
              <div class="admin-info-request-label">Request ID</div>
              <div id="modalHeading" class="admin-info-request-id">#000001</div>
            </div>
            <div class="admin-info-found-badge">
              <span class="admin-found-dot"></span>
              <span id="modalFoundAt">Found 10/11/2026 10:08:55</span>
            </div>
          </div>

          <!-- Row: Username & Full Name -->
          <div class="admin-field-row-2">
            <div class="admin-field-item">
              <span class="admin-field-label">Username</span>
              <span id="infoUser" class="admin-field-val">uccloud26</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Full Name</span>
              <span id="infoFullName" class="admin-field-val">bukan manusia biasa</span>
            </div>
          </div>

          <!-- Row: Address & Postal Code -->
          <div class="admin-field-row-2">
            <div class="admin-field-item">
              <span class="admin-field-label">Address</span>
              <span id="infoAddress" class="admin-field-val truncate">Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Postal Code</span>
              <span id="infoPostalCode" class="admin-field-val">1945</span>
            </div>
          </div>

          <!-- Row: Phone Number -->
          <div class="admin-field-item">
            <span class="admin-field-label">Phone Number</span>
            <span id="infoPhone" class="admin-field-val">0813 888 8888</span>
          </div>

          <!-- Row: Artist/Band, Merch Type, Released Year -->
          <div class="admin-field-row-3">
            <div class="admin-field-item">
              <span class="admin-field-label">Artist/Band</span>
              <span id="infoArtistBand" class="admin-field-val">Taylor Swift</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Merch Type</span>
              <span id="infoMerchType" class="admin-field-val">T-Shirt</span>
            </div>
            <div class="admin-field-item">
              <span class="admin-field-label">Released Year</span>
              <span id="infoReleasedYear" class="admin-field-val">2016</span>
            </div>
          </div>

          <!-- Row: Merch Edition -->
          <div class="admin-field-item">
            <span class="admin-field-label">Merch Edition</span>
            <span id="infoEdition" class="admin-field-val">Taylor Swift North American Tour</span>
          </div>

          <!-- Row: More Description -->
          <div class="admin-field-item">
            <span class="admin-field-label">More Description</span>
            <span id="infoDesc" class="admin-field-val">Taylor Swift North American Tour 2016, Long Sleeve, White with tour list</span>
          </div>

          <!-- Input: Status Dropdown -->
          <div class="admin-form-group">
            <label for="modalStatus" class="admin-form-label">Status</label>
            <div class="admin-select-wrapper">
              <select id="modalStatus" class="admin-form-select">
                <option value="In Progress">In Progress</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
                <option value="Pending">Pending</option>
              </select>
              <span class="admin-select-arrow">v</span>
            </div>
          </div>

          <!-- Input: Price -->
          <div class="admin-form-group">
            <label for="modalPrice" class="admin-form-label">Price</label>
            <input type="text" id="modalPrice" class="admin-form-input" placeholder="-" />
          </div>

          <!-- Input: Seller Notes -->
          <div class="admin-form-group">
            <label for="modalNotes" class="admin-form-label">Seller Notes</label>
            <input type="text" id="modalNotes" class="admin-form-input" placeholder="-" />
          </div>

          <!-- Action Buttons: Save (Blue) & Cancel (Red) -->
          <div class="admin-modal-actions">
            <button
              type="button"
              id="btnSaveModal"
              onclick="submitStatusUpdate()"
              class="admin-btn-save-modal"
            >
              <span id="labelSaveModal">Save</span>
              <span id="spinSaveModal" class="hidden">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
              </span>
            </button>

            <button
              type="button"
              onclick="closeAdminModal()"
              class="admin-btn-cancel-modal"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- ================= FOOTER (Matching admin_page1.png) ================= -->
  <footer class="admin-footer">
    instagram &nbsp;|&nbsp; tiktok &nbsp;|&nbsp; threads &nbsp;|&nbsp; x
  </footer>
</div>
@endsection

@section('scripts')
<script>
  let adminSubmissionsList = [];
  let selectedId = null;

  function toggleAdminDropdown() {
    const d = document.getElementById('adminDropdown');
    d.classList.toggle('hidden');
  }

  document.addEventListener('click', function(e) {
    const wrap = document.getElementById('adminMenuWrapper');
    const d = document.getElementById('adminDropdown');
    if (wrap && d && !wrap.contains(e.target)) {
      d.classList.add('hidden');
    }
  });

  function handleAdminLogout() {
    localStorage.removeItem('wm_user');
    localStorage.removeItem('rakap_token');
    window.location.href = '/auth';
  }

  function formatDate(dateStr) {
    if (!dateStr) return '10/10/2026';
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  }

  async function loadAdminSubmissions() {
    const tbody = document.getElementById('adminTableBody');
    const token = localStorage.getItem('rakap_token');

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
        tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">Gagal memuat data formulir.</td></tr>`;
      }
    } catch(err) {
      tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">Koneksi terputus ke server.</td></tr>`;
    }
  }

  function renderAdminTable(data) {
    const tbody = document.getElementById('adminTableBody');
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
      <tr class="hover:bg-slate-50/70 transition-colors text-xs sm:text-sm text-slate-800">
        <td class="py-4 px-3 font-normal">${idx + 1}</td>
        <td class="py-4 px-4 font-normal">${sub.requestId || String(idx + 1).padStart(7, '0')}</td>
        <td class="py-4 px-4 font-normal">${sub.submittedBy?.username || sub.submittedBy?.name || 'uccloud26'}</td>
        <td class="py-4 px-4 font-normal">${sub.artistBand || sub.title || 'Taking Back Sunday'}</td>
        <td class="py-4 px-4 font-normal">${sub.merchType || sub.category || 'Vinyl'}</td>
        <td class="py-4 px-4 font-normal">${sub.releasedYear || '2009'}</td>
        <td class="py-4 px-4 font-normal">${sub.status || 'In Progress'}</td>
        <td class="py-4 px-4 font-normal">${formatDate(sub.createdAt || sub.created_at)}</td>
        <td class="py-4 px-3 text-center">
          <button
            type="button"
            onclick="openAdminModal(${sub.id})"
            class="p-1 text-slate-700 hover:text-[#4e46e5] hover:scale-110 transition-all cursor-pointer"
            title="Lihat Detail & Review"
          >
            <i data-lucide="info" class="w-4.5 h-4.5"></i>
          </button>
        </td>
      </tr>
    `).join('');

    lucide.createIcons();
  }

  function selectThumbImage(el) {
    const img = el.querySelector('img');
    if (img) {
      document.getElementById('modalPreviewImg').src = img.src;
    }
  }

  function openAdminModal(id) {
    const sub = adminSubmissionsList.find(s => s.id === id);
    if (!sub) return;

    selectedId = sub.id;
    document.getElementById('modalHeading').textContent = `#${sub.requestId || String(sub.id).padStart(6, '0')}`;
    document.getElementById('modalFoundAt').textContent = `Found ${sub.foundAt || '10/11/2026 10:08:55'}`;

    document.getElementById('infoUser').textContent = sub.submittedBy?.username || sub.submittedBy?.name || 'uccloud26';
    document.getElementById('infoFullName').textContent = sub.fullName || 'bukan manusia biasa';
    document.getElementById('infoAddress').textContent = sub.address || 'Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx';
    document.getElementById('infoPostalCode').textContent = sub.postalCode || '1945';
    document.getElementById('infoPhone').textContent = sub.phoneNumber || '0813 888 8888';

    document.getElementById('infoArtistBand').textContent = sub.artistBand || sub.title || 'Taylor Swift';
    document.getElementById('infoMerchType').textContent = sub.merchType || sub.category || 'T-Shirt';
    document.getElementById('infoReleasedYear').textContent = sub.releasedYear || '2016';
    document.getElementById('infoEdition').textContent = sub.merchEdition || 'Taylor Swift North American Tour';
    document.getElementById('infoDesc').textContent = sub.description || 'Taylor Swift North American Tour 2016, Long Sleeve, White with tour list';

    document.getElementById('modalStatus').value = sub.status || 'In Progress';
    document.getElementById('modalPrice').value = sub.price && sub.price !== '-' ? sub.price : '-';
    document.getElementById('modalNotes').value = sub.sellerNotes && sub.sellerNotes !== '-' ? sub.sellerNotes : (sub.adminNotes || '-');

    const mainImg = sub.imageUrl || '/assets/poster_art.png';
    document.getElementById('modalPreviewImg').src = mainImg;
    document.getElementById('thumb1').src = mainImg;
    document.getElementById('thumb2').src = mainImg;
    document.getElementById('thumb3').src = mainImg;

    document.getElementById('adminReviewModal').classList.remove('hidden');
    lucide.createIcons();
  }

  function closeAdminModal() {
    document.getElementById('adminReviewModal').classList.add('hidden');
    selectedId = null;
  }

  async function submitStatusUpdate() {
    if (!selectedId) return;

    const newStatus = document.getElementById('modalStatus').value;
    const price = document.getElementById('modalPrice').value.trim();
    const sellerNotes = document.getElementById('modalNotes').value.trim();

    const btn = document.getElementById('btnSaveModal');
    const label = document.getElementById('labelSaveModal');
    const spin = document.getElementById('spinSaveModal');

    btn.disabled = true;
    label.textContent = 'Saving...';
    spin.classList.remove('hidden');

    try {
      const token = localStorage.getItem('rakap_token');
      const response = await fetch(`/api/admin/submissions/${selectedId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: JSON.stringify({
          status: newStatus,
          price: price,
          sellerNotes: sellerNotes,
          seller_notes: sellerNotes,
          adminNotes: sellerNotes,
          admin_notes: sellerNotes
        })
      });

      const res = await response.json();

      if (res.success) {
        closeAdminModal();
        loadAdminSubmissions();
        showToast(`Request #${selectedId} berhasil diperbarui.`);
      } else {
        alert(res.message || 'Gagal menyimpan status.');
      }
    } catch(e) {
      alert('Terjadi kesalahan jaringan.');
    } finally {
      btn.disabled = false;
      label.textContent = 'Save';
      spin.classList.add('hidden');
      lucide.createIcons();
    }
  }

  // Sync admin user info
  (function syncAdminInfo() {
    const stored = localStorage.getItem('wm_user');
    if (stored) {
      try {
        const u = JSON.parse(stored);
        if (u.name || u.username) document.getElementById('adminDropdownName').textContent = u.name || u.username;
        if (u.email) document.getElementById('adminDropdownEmail').textContent = u.email;
      } catch(e) {}
    }
  })();

  loadAdminSubmissions();
</script>
@endsection

