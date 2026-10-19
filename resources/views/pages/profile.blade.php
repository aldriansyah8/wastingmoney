@extends('layouts.app')

@section('title', 'wastingmoney - Edit Profile')

@section('styles')
  <!-- Dedicated CSS for Profile Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/profile.css">
@endsection

@section('content')
<div class="profile-page-container selection:bg-indigo-100 selection:text-indigo-800">

  <!-- Reusable Header Partial -->
  @include('partials.header')

  <!-- ================= MAIN EDIT PROFILE FORM (Matching Image 1) ================= -->
  <main class="profile-main-content">

    <!-- Incomplete Profile Warning Alert -->
    <div id="incompleteProfileAlert" class="hidden mb-6 p-4 rounded-2xl border border-amber-300 bg-amber-50 text-amber-900 text-xs sm:text-sm flex items-start gap-3 shadow-sm">
      <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
      <div>
        <p class="font-bold text-slate-900 text-sm">Harap Lengkapi Profil Anda Terlebih Dahulu</p>
        <p class="text-xs text-slate-600 mt-1">Sebelum melanjutkan membuat request atau menjelajahi katalog, mohon lengkapi Nama Lengkap, Alamat, Nomor Telepon, dan Kode Pos Anda di bawah ini lalu klik Save Changes.</p>
      </div>
    </div>
    
    <form id="profileEditForm" class="profile-form" onsubmit="event.preventDefault(); promptConfirmSave();">
      @csrf

      <!-- 1. Username (Read-Only / Disabled Look) -->
      <div class="profile-form-group">
        <label for="profileUsername" class="profile-form-label">Username</label>
        <input 
          type="text" 
          id="profileUsername" 
          name="username" 
          value="uccloud26" 
          readonly 
          tabindex="-1"
          class="profile-input profile-input-readonly" 
        />
      </div>

      <!-- 2. Full Name -->
      <div class="profile-form-group">
        <label for="profileFullName" class="profile-form-label">Full Name</label>
        <input 
          type="text" 
          id="profileFullName" 
          name="full_name" 
          placeholder="Enter your fullname" 
          autocomplete="name"
          class="profile-input" 
        />
      </div>

      <!-- 3. Address -->
      <div class="profile-form-group">
        <label for="profileAddress" class="profile-form-label">Address</label>
        <input 
          type="text" 
          id="profileAddress" 
          name="address" 
          placeholder="Enter your full address" 
          autocomplete="street-address"
          class="profile-input" 
        />
      </div>

      <!-- 4. Phone Number & Postal Code (2 Columns) -->
      <div class="profile-grid-2-col">
        <div class="profile-form-group">
          <label for="profilePhone" class="profile-form-label">Phone Number</label>
          <input 
            type="text" 
            id="profilePhone" 
            name="phone_number" 
            placeholder="0813xxxxxxxx" 
            autocomplete="tel"
            class="profile-input" 
          />
        </div>

        <div class="profile-form-group">
          <label for="profilePostalCode" class="profile-form-label">Postal Code</label>
          <input 
            type="text" 
            id="profilePostalCode" 
            name="postal_code" 
            placeholder="Enter your postal code" 
            autocomplete="postal-code"
            class="profile-input" 
          />
        </div>
      </div>

      <!-- 5. Password & Change Password Link -->
      <div class="profile-form-group">
        <label for="profilePassword" class="profile-form-label">Password</label>
        <input 
          type="password" 
          id="profilePassword" 
          value="******" 
          readonly 
          tabindex="-1"
          class="profile-input profile-input-readonly" 
        />
        <a 
          href="javascript:void(0)" 
          class="profile-change-pwd-link" 
          onclick="togglePasswordDrawer()"
          id="togglePwdLink"
        >
          Change Password?
        </a>

        <!-- Expandable Password Change Drawer -->
        <div id="passwordDrawer" class="profile-password-drawer hidden">
          <div class="profile-form-group">
            <label class="text-xs font-semibold text-slate-700 mb-1">New Password</label>
            <input 
              type="password" 
              id="profileNewPassword" 
              placeholder="Enter new password (min. 6 characters)" 
              class="profile-input text-xs" 
            />
          </div>
          <div class="profile-form-group">
            <label class="text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
            <input 
              type="password" 
              id="profileConfirmPassword" 
              placeholder="Re-enter new password" 
              class="profile-input text-xs" 
            />
          </div>
        </div>
      </div>

      <!-- 6. Action Buttons: Save Changes & Cancel (Matching Image 1) -->
      <div class="profile-actions-row">
        <button 
          type="button" 
          class="profile-btn-save" 
          onclick="promptConfirmSave()"
        >
          Save Changes
        </button>

        <button 
          type="button" 
          class="profile-btn-cancel" 
          onclick="handleCancel()"
        >
          Cancel
        </button>
      </div>

    </form>

  </main>

  <!-- ================= CONFIRMATION MODAL (Matching Image 2) ================= -->
  <div 
    id="profileConfirmModalOverlay" 
    class="profile-confirm-overlay" 
    onclick="handleModalBackdropClick(event)"
    role="dialog" 
    aria-modal="true"
  >
    <div class="profile-confirm-card" onclick="event.stopPropagation()">
      <h3 class="profile-confirm-title">
        Are you sure want to save changes?
      </h3>

      <div class="profile-confirm-actions">
        <button 
          type="button" 
          class="profile-btn-yes" 
          onclick="executeSaveProfile()"
          id="btnConfirmYes"
        >
          Yes
        </button>

        <button 
          type="button" 
          class="profile-btn-modal-cancel" 
          onclick="closeConfirmModal()"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- Reusable Footer Partial -->
  @include('partials.footer')

</div>
@endsection

@section('scripts')
<script src="/js/profile.js"></script>
@endsection

