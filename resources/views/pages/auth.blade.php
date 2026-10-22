@extends('layouts.app')

@section('title', 'wastingmoney - Auth')

@section('styles')
  <!-- Dedicated CSS for Auth Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/auth.css">
@endsection

@section('content')
<div class="auth-page-container">
  
  <!-- Reusable Header Partial -->
  @include('partials.header')

  <!-- ================= MAIN CONTAINER ================= -->
  <main class="auth-main-container">
    <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 xl:gap-16 items-center">
      
      <!-- Left Column: 3D Character Illustration -->
      <div class="flex lg:col-span-6 xl:col-span-7 justify-center items-center py-2 sm:py-4">
        <div class="relative select-none flex justify-center">
          <img
            src="/assets/character.png"
            alt="Illustration"
            class="auth-character-img"
          />
        </div>
      </div>

      <!-- Right Column: Form Card -->
      <div class="w-full lg:col-span-6 xl:col-span-5 flex justify-center lg:justify-end">
        <div class="auth-form-card">
          
          <!-- Alert Feedback Messages -->
          <div id="authErrorAlert" class="hidden mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-start gap-2.5">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
            <span id="authErrorMsg">Username/Email dan Password wajib diisi.</span>
          </div>

          <div id="authSuccessAlert" class="hidden mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
            <span id="authSuccessMsg">Login berhasil!</span>
          </div>

          <!-- ================= SIGN IN FORM ================= -->
          <div id="signInSection">
            <div class="mb-6 sm:mb-8">
              <h1 class="auth-card-title">
                Sign in to
              </h1>
              <p class="auth-card-subtitle">
                wasting your <strong class="font-extrabold text-slate-900">money.</strong>
              </p>
            </div>

            <form id="signInForm" class="space-y-4">
              @csrf

              <!-- Username Field -->
              <div>
                <label class="auth-input-label">
                  Username
                </label>
                <input
                  type="text"
                  id="signInUsername"
                  name="email"
                  placeholder="Enter your username"
                  required
                  autocomplete="username"
                  class="auth-text-input"
                />
              </div>

              <!-- Password Field -->
              <div>
                <label class="auth-input-label">
                  Password
                </label>
                <div class="relative">
                  <input
                    type="password"
                    id="signInPassword"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                    class="auth-text-input pr-11"
                  />
                  <button
                    type="button"
                    onclick="togglePassword('signInPassword', 'eyeIconIn')"
                    class="auth-password-toggle-btn"
                    tabindex="-1"
                  >
                    <i id="eyeIconIn" data-lucide="eye" class="w-4 h-4"></i>
                  </button>
                </div>

                <!-- Forgot password -->
                <div class="text-right mt-2">
                  <button
                    type="button"
                    onclick="alert('Fitur reset password: Anda dapat menghubungi Admin di sysadmin@wastingmoney.id')"
                    class="text-xs text-slate-400 hover:text-[#4e46e5] transition-colors cursor-pointer bg-transparent border-0"
                  >
                    Forgot password?
                  </button>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                id="btnSignIn"
                class="auth-submit-btn"
              >
                <span id="labelSignIn">Login</span>
                <span id="spinSignIn" class="hidden">
                  <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                </span>
              </button>

              <!-- Switch to Sign Up -->
              <p class="mt-6 text-center text-xs sm:text-sm text-slate-600">
                If you don't have an account,
                <button
                  type="button"
                  onclick="setAuthMode('signup')"
                  class="auth-switch-link"
                >
                  register here!
                </button>
              </p>
            </form>
          </div>

          <!-- ================= SIGN UP FORM ================= -->
          <div id="signUpSection" class="hidden">
            <div class="mb-6 sm:mb-8">
              <h1 class="auth-card-title">
                Sign up to
              </h1>
              <p class="auth-card-subtitle">
                hunt your <strong class="font-extrabold text-slate-900">goods.</strong>
              </p>
            </div>

            <form id="signUpForm" class="space-y-4" autocomplete="off">
              @csrf

              <!-- Email Field -->
              <div>
                <label class="auth-input-label">
                  Email
                </label>
                <input
                  type="email"
                  id="signUpEmail"
                  name="email"
                  placeholder="example@google.com"
                  required
                  autocomplete="off"
                  class="auth-text-input"
                />
              </div>

              <!-- Username Field -->
              <div>
                <label class="auth-input-label">
                  Username
                </label>
                <input
                  type="text"
                  id="signUpUsername"
                  name="username"
                  placeholder="Enter your username"
                  required
                  autocomplete="off"
                  class="auth-text-input"
                />
              </div>

              <!-- Password Field -->
              <div>
                <label class="auth-input-label">
                  Password
                </label>
                <div class="relative">
                  <input
                    type="password"
                    id="signUpPassword"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="new-password"
                    class="auth-text-input pr-11"
                  />
                  <button
                    type="button"
                    onclick="togglePassword('signUpPassword', 'eyeIconUp')"
                    class="auth-password-toggle-btn"
                    tabindex="-1"
                  >
                    <i id="eyeIconUp" data-lucide="eye" class="w-4 h-4"></i>
                  </button>
                </div>
              </div>

              <!-- Re-type Password Field -->
              <div>
                <label class="auth-input-label">
                  Re-type Password
                </label>
                <div class="relative">
                  <input
                    type="password"
                    id="signUpConfirmPassword"
                    placeholder="Enter your password"
                    required
                    autocomplete="new-password"
                    class="auth-text-input pr-11"
                  />
                  <button
                    type="button"
                    onclick="togglePassword('signUpConfirmPassword', 'eyeIconConfirmUp')"
                    class="auth-password-toggle-btn"
                    tabindex="-1"
                    title="Lihat / Sembunyikan Password"
                  >
                    <i id="eyeIconConfirmUp" data-lucide="eye" class="w-4 h-4"></i>
                  </button>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                id="btnSignUp"
                class="auth-submit-btn"
              >
                <span id="labelSignUp">Sign Up</span>
                <span id="spinSignUp" class="hidden">
                  <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                </span>
              </button>

              <!-- Switch to Sign In -->
              <p class="mt-6 text-center text-xs sm:text-sm text-slate-600">
                If you already have an account,
                <button
                  type="button"
                  onclick="setAuthMode('signin')"
                  class="auth-switch-link"
                >
                  login here!
                </button>
              </p>
            </form>
          </div>

        </div>
      </div>

    </div>
  </main>

  <div class="pb-6 sm:pb-8"></div>
</div>
@endsection

@section('scripts')
<script src="/js/auth.js"></script>
@endsection

