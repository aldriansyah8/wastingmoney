<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'wastingmoney')</title>

  <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Global Base CSS -->
  <link rel="stylesheet" href="/css/base.css">

  @yield('styles')
</head>
<body class="min-h-screen bg-white flex flex-col justify-between selection:bg-indigo-100 selection:text-indigo-800">

  <!-- Main Content (Each page controls its exact client-designed header & body) -->
  @yield('content')

  <!-- Toast Notification Container -->
  <div id="globalToast" class="fixed bottom-6 right-6 z-50 hidden transition-all duration-300 transform translate-y-4 opacity-0">
    <div class="bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-sm">
      <i id="toastIcon" data-lucide="info" class="w-4 h-4 text-indigo-400"></i>
      <span id="toastMessage">Pemberitahuan</span>
    </div>
  </div>

  <script>
    // Inisialisasi ikon Lucide secara otomatis
    lucide.createIcons();

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

    // Event listener: Menutup dropdown jika pengguna mengklik di luar wrapper dropdown
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.wm-dropdown-wrapper')) {
        document.querySelectorAll('.wm-dropdown-menu').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.wm-dropdown-trigger').forEach(b => b.classList.remove('open'));
      }
    });

    // Event listener: Menutup dropdown jika tombol Escape ditekan
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.wm-dropdown-menu').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.wm-dropdown-trigger').forEach(b => b.classList.remove('open'));
      }
    });
  </script>

  @yield('scripts')
</body>
</html>
