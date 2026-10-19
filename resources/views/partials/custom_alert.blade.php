{{-- 
  wastingmoney - Custom Designed Alert & Confirmation Modal
  File: resources/views/partials/custom_alert.blade.php
  
  Pengganti alert() bawaan browser dengan tampilan UI/UX modern,
  sesuai dengan desain dan estetika wastingmoney.
--}}

<div 
  id="wmCustomAlertOverlay" 
  class="wm-custom-alert-overlay" 
  onclick="handleCustomAlertBackdrop(event)"
  role="dialog" 
  aria-modal="true"
  aria-labelledby="wmAlertTitle"
  aria-describedby="wmAlertMessage"
>
  <div class="wm-custom-alert-card" onclick="event.stopPropagation()">
    <!-- Icon Indicator Badge -->
    <div id="wmAlertIconWrapper" class="wm-alert-icon-wrapper warning">
      <i id="wmAlertIcon" data-lucide="user-cog" class="w-6 h-6"></i>
    </div>

    <!-- Title -->
    <h3 id="wmAlertTitle" class="wm-alert-title">
      Lengkapi Data Diri Anda
    </h3>

    <!-- Message -->
    <p id="wmAlertMessage" class="wm-alert-message">
      Anda harus melengkapi data diri Anda terlebih dahulu di Edit Profile sebelum dapat mengajukan request.
    </p>

    <!-- Draft Saved Notice (Shows when user form data is safely saved) -->
    <div id="wmAlertDraftNotice" class="wm-alert-draft-notice hidden">
      <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-emerald-600"></i>
      <span>Formulir Anda telah otomatis diamankan sebagai draf dan akan kembali terisi saat Anda membuka halaman ini lagi.</span>
    </div>

    <!-- Actions Area -->
    <div class="wm-alert-actions">
      <button 
        type="button" 
        id="wmAlertSecondaryBtn"
        class="wm-btn-alert-secondary" 
        onclick="closeCustomAlert()"
      >
        Nanti Saja
      </button>

      <button 
        type="button" 
        id="wmAlertPrimaryBtn"
        class="wm-btn-alert-primary" 
        onclick="executeCustomAlertConfirm()"
      >
        <span id="wmAlertPrimaryText">Edit Profile Sekarang</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </button>
    </div>
  </div>
</div>
