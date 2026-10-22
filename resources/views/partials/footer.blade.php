{{-- 
  wastingmoney - Reusable Footer Partial
  File: resources/views/partials/footer.blade.php
  
  Mendukung otomatis deteksi kelas CSS footer (home-footer, profile-footer, user-list-footer, admin-footer)
  atau dapat dispesifikasikan langsung: @include('partials.footer', ['class' => 'home-footer'])
--}}

@php
  $resolvedClass = $class ?? (
    request()->is('admin*') ? 'admin-footer' : (
      request()->is('list*') || request()->is('requests*') || request()->is('catalog*') ? 'user-list-footer' : (
        request()->is('profile*') || request()->is('account*') ? 'profile-footer' : 'home-footer'
      )
    )
  );
@endphp

<!-- ================= FOOTER ================= -->
<footer class="{{ $resolvedClass }}">
  instagram &nbsp;|&nbsp; tiktok &nbsp;|&nbsp; threads &nbsp;|&nbsp; x
</footer>
