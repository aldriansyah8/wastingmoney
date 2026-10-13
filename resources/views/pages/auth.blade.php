@extends('layouts.app')

@section('title', 'wastingmoney - Auth')

@section('styles')
  <!-- Dedicated CSS for Auth Page (Separated from Blade) -->
  <link rel="stylesheet" href="/css/auth.css">
@endsection

@section('content')
<div class="auth-page-container">
  
  <!-- ================= TOP HEADER (Matching Sign in.png) ================= -->
  <header class="auth-header">
    <div class="flex items-center select-none">
      <img 
        src="/assets/logo.png" 
        alt="wastingmoney" 
        class="auth-logo-img" 
      />
    </div>
  </header>

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
                    onclick="alert('Fitur reset password: Anda dapat menghubungi Admin di sysadmin@rakapteman.id')"
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

            <form id="signUpForm" class="space-y-4">
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
                  autocomplete="email"
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
                <input
                  type="password"
                  id="signUpConfirmPassword"
                  placeholder="Enter your password"
                  required
                  autocomplete="new-password"
                  class="auth-text-input"
                />
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                id="btnSignUp"
                class="auth-submit-btn"
              >
                <span id="labelSignUp">Login</span>
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
<script>
  function setAuthMode(mode) {
    const err = document.getElementById('authErrorAlert');
    const succ = document.getElementById('authSuccessAlert');
    err.classList.add('hidden');
    succ.classList.add('hidden');

    if (mode === 'signup') {
      document.getElementById('signInSection').classList.add('hidden');
      document.getElementById('signUpSection').classList.remove('hidden');
    } else {
      document.getElementById('signUpSection').classList.add('hidden');
      document.getElementById('signInSection').classList.remove('hidden');
    }
    lucide.createIcons();
  }

  function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
      input.type = 'text';
      icon.setAttribute('data-lucide', 'eye-off');
    } else {
      input.type = 'password';
      icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
  }

  // Handle Sign In
  document.getElementById('signInForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const err = document.getElementById('authErrorAlert');
    const errMsg = document.getElementById('authErrorMsg');
    const succ = document.getElementById('authSuccessAlert');
    const succMsg = document.getElementById('authSuccessMsg');
    const btn = document.getElementById('btnSignIn');
    const label = document.getElementById('labelSignIn');
    const spin = document.getElementById('spinSignIn');

    err.classList.add('hidden');
    succ.classList.add('hidden');

    const username = document.getElementById('signInUsername').value.trim();
    const password = document.getElementById('signInPassword').value;

    btn.disabled = true;
    label.textContent = 'Memproses...';
    spin.classList.remove('hidden');

    try {
      const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: username,
          password: password
        })
      });

      const res = await response.json();

      if (res.success && res.data) {
        localStorage.setItem('rakap_token', res.data.token);
        localStorage.setItem('wm_user', JSON.stringify(res.data.user));
        succMsg.textContent = 'Login berhasil!';
        succ.classList.remove('hidden');

        setTimeout(() => {
          if (res.data.user.role === 'admin') {
            window.location.href = '/admin';
          } else {
            window.location.href = '/';
          }
        }, 500);
      } else {
        errMsg.textContent = res.message || 'Login gagal. Periksa kembali username & password.';
        err.classList.remove('hidden');
      }
    } catch(e) {
      errMsg.textContent = 'Gagal menghubungi server.';
      err.classList.remove('hidden');
    } finally {
      btn.disabled = false;
      label.textContent = 'Login';
      spin.classList.add('hidden');
      lucide.createIcons();
    }
  });

  // Handle Sign Up
  document.getElementById('signUpForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const err = document.getElementById('authErrorAlert');
    const errMsg = document.getElementById('authErrorMsg');
    const succ = document.getElementById('authSuccessAlert');
    const succMsg = document.getElementById('authSuccessMsg');
    const btn = document.getElementById('btnSignUp');
    const label = document.getElementById('labelSignUp');
    const spin = document.getElementById('spinSignUp');

    err.classList.add('hidden');
    succ.classList.add('hidden');

    const email = document.getElementById('signUpEmail').value.trim();
    const username = document.getElementById('signUpUsername').value.trim();
    const password = document.getElementById('signUpPassword').value;
    const confirmPassword = document.getElementById('signUpConfirmPassword').value;

    if (password.length < 6) {
      errMsg.textContent = 'Password minimal 6 karakter.';
      err.classList.remove('hidden');
      return;
    }

    if (password !== confirmPassword) {
      errMsg.textContent = 'Konfirmasi password (Re-type Password) tidak cocok.';
      err.classList.remove('hidden');
      return;
    }

    btn.disabled = true;
    label.textContent = 'Mendaftarkan...';
    spin.classList.remove('hidden');

    try {
      const response = await fetch('/api/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: email.toLowerCase(),
          username: username,
          password: password,
          role: 'user'
        })
      });

      const res = await response.json();

      if (res.success && res.data) {
        localStorage.setItem('rakap_token', res.data.token);
        localStorage.setItem('wm_user', JSON.stringify(res.data.user));
        succMsg.textContent = 'Registrasi berhasil! Akun Anda telah dibuat.';
        succ.classList.remove('hidden');

        setTimeout(() => {
          window.location.href = '/';
        }, 600);
      } else {
        errMsg.textContent = res.message || 'Registrasi gagal. Silakan coba kembali.';
        err.classList.remove('hidden');
      }
    } catch(e) {
      errMsg.textContent = 'Gagal menghubungi server.';
      err.classList.remove('hidden');
    } finally {
      btn.disabled = false;
      label.textContent = 'Login';
      spin.classList.add('hidden');
      lucide.createIcons();
    }
  });
</script>
@endsection
