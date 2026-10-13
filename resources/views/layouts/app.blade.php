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
    // Initialize Lucide Icons
    lucide.createIcons();

    // Global Toast Trigger
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
  </script>

  @yield('scripts')
</body>
</html>
