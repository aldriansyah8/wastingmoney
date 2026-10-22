/**
 * wastingmoney - Auth Page Script (Sign In & Sign Up)
 * File: public/js/auth.js
 * 
 * Modul JavaScript terpisah khusus untuk halaman autentikasi (/auth & /login).
 * Menangani toggle tab mode (Sign In / Sign Up), toggle lihat password,
 * serta submit form Sign In dan Sign Up ke backend API Laravel.
 */

// Inisialisasi ikon lucide saat file dimuat
if (window.lucide) {
  lucide.createIcons();
}

/**
 * Mengganti tampilan form antara tab "Sign In" dan "Sign Up".
 * Mereset alert error dan sukses setiap kali berpindah tab.
 * 
 * @param {'signin'|'signup'} mode Tab tujuan yang ingin ditampilkan
 */
function setAuthMode(mode) {
  const err = document.getElementById('authErrorAlert');
  const succ = document.getElementById('authSuccessAlert');
  if (err) err.classList.add('hidden');
  if (succ) succ.classList.add('hidden');

  const signInSection = document.getElementById('signInSection');
  const signUpSection = document.getElementById('signUpSection');

  if (mode === 'signup') {
    if (signInSection) signInSection.classList.add('hidden');
    if (signUpSection) signUpSection.classList.remove('hidden');
  } else {
    if (signUpSection) signUpSection.classList.add('hidden');
    if (signInSection) signInSection.classList.remove('hidden');
  }

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.setAuthMode = setAuthMode;

/**
 * Menampilkan atau menyembunyikan karakter password pada input form.
 * Mengubah tipe input antara 'password' dan 'text', serta mengubah ikon eye.
 * 
 * @param {string} inputId ID elemen input password
 * @param {string} iconId ID elemen ikon Lucide
 */
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.setAttribute('data-lucide', 'eye-off');
  } else {
    input.type = 'password';
    icon.setAttribute('data-lucide', 'eye');
  }

  if (window.lucide) {
    lucide.createIcons();
  }
}
window.togglePassword = togglePassword;

/**
 * Event Listener Form Sign In:
 * Mengirimkan kredensial (username/email dan password) ke endpoint backend /api/auth/login.
 * Jika login sukses, token dan data user disimpan di localStorage dan sesi sinkron,
 * lalu dialihkan ke dashboard yang sesuai (admin ke /admin, user ke beranda /).
 */
document.addEventListener('DOMContentLoaded', function() {
  const signInForm = document.getElementById('signInForm');
  if (signInForm) {
    signInForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      const err = document.getElementById('authErrorAlert');
      const errMsg = document.getElementById('authErrorMsg');
      const succ = document.getElementById('authSuccessAlert');
      const succMsg = document.getElementById('authSuccessMsg');
      const btn = document.getElementById('btnSignIn');
      const label = document.getElementById('labelSignIn');
      const spin = document.getElementById('spinSignIn');

      if (err) err.classList.add('hidden');
      if (succ) succ.classList.add('hidden');

      const username = document.getElementById('signInUsername').value.trim();
      const password = document.getElementById('signInPassword').value;

      if (btn) btn.disabled = true;
      if (label) label.textContent = 'Memproses...';
      if (spin) spin.classList.remove('hidden');

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
          localStorage.setItem('wm_token', res.data.token);
          localStorage.setItem('wm_user', JSON.stringify(res.data.user));
          if (succMsg) succMsg.textContent = 'Login berhasil!';
          if (succ) succ.classList.remove('hidden');

          setTimeout(() => {
            const u = res.data.user;
            const isAdmin = u.role === 'admin' || u.role === 'superadmin' || (u.email && u.email.includes('admin'));

            if (isAdmin) {
              window.location.href = '/admin';
            } else {
              window.location.href = '/';
            }
          }, 500);
        } else {
          if (errMsg) errMsg.textContent = res.message || 'Login gagal. Periksa kembali username & password.';
          if (err) err.classList.remove('hidden');
        }
      } catch(e) {
        if (errMsg) errMsg.textContent = 'Gagal menghubungi server.';
        if (err) err.classList.remove('hidden');
      } finally {
        if (btn) btn.disabled = false;
        if (label) label.textContent = 'Login';
        if (spin) spin.classList.add('hidden');
        if (window.lucide) lucide.createIcons();
      }
    });
  }

  /**
   * Event Listener Form Sign Up:
   * Melakukan validasi input registrasi (kecocokan konfirmasi password, panjang karakter),
   * lalu mengirimkan data pendaftaran ke endpoint backend /api/auth/register.
   * Setelah registrasi sukses, tampilan otomatis berpindah ke form login (Sign In)
   * dengan pesan sukses dan nama akun yang otomatis terisi.
   */
  const signUpForm = document.getElementById('signUpForm');
  if (signUpForm) {
    signUpForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      const err = document.getElementById('authErrorAlert');
      const errMsg = document.getElementById('authErrorMsg');
      const succ = document.getElementById('authSuccessAlert');
      const succMsg = document.getElementById('authSuccessMsg');
      const btn = document.getElementById('btnSignUp');
      const label = document.getElementById('labelSignUp');
      const spin = document.getElementById('spinSignUp');

      if (err) err.classList.add('hidden');
      if (succ) succ.classList.add('hidden');

      const email = document.getElementById('signUpEmail').value.trim();
      const username = document.getElementById('signUpUsername').value.trim();
      const password = document.getElementById('signUpPassword').value;
      const confirmPassword = document.getElementById('signUpConfirmPassword').value;

      if (password.length < 6) {
        if (errMsg) errMsg.textContent = 'Password minimal 6 karakter.';
        if (err) err.classList.remove('hidden');
        return;
      }

      if (password !== confirmPassword) {
        if (errMsg) errMsg.textContent = 'Konfirmasi password (Re-type Password) tidak cocok.';
        if (err) err.classList.remove('hidden');
        return;
      }

      if (btn) btn.disabled = true;
      if (label) label.textContent = 'Mendaftarkan...';
      if (spin) spin.classList.remove('hidden');

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

        if (res.success) {
          // Bersihkan sesi lama dari storage agar akun baru tidak terinterferensi sesi sebelumnya
          localStorage.removeItem('wm_user');
          localStorage.removeItem('wm_token');
          localStorage.removeItem('rakap_token');
          localStorage.removeItem('wm_user_token');
          localStorage.removeItem('wm_user_data');
          localStorage.removeItem('wm_user_profile');

          // Reset form sign up
          signUpForm.reset();

          // Berpindah ke tampilan Sign In sesuai permintaan: "ketika user sudah registrasi itu akan berpindah ke halaman login"
          setAuthMode('signin');

          // Isi otomatis username/email di form login untuk kemudahan pengguna
          const signInUsername = document.getElementById('signInUsername');
          if (signInUsername) {
            signInUsername.value = username || email;
          }

          // Kosongkan dan fokuskan kolom password login
          const signInPassword = document.getElementById('signInPassword');
          if (signInPassword) {
            signInPassword.value = '';
            signInPassword.focus();
          }

          if (succMsg) succMsg.textContent = res.message || 'Registrasi berhasil! Silakan cek kotak masuk Gmail Anda untuk verifikasi email sebelum login.';
          if (succ) succ.classList.remove('hidden');
        } else {
          if (errMsg) errMsg.textContent = res.message || 'Registrasi gagal. Silakan coba kembali.';
          if (err) err.classList.remove('hidden');
        }
      } catch(e) {
        if (errMsg) errMsg.textContent = 'Gagal menghubungi server.';
        if (err) err.classList.remove('hidden');
      } finally {
        if (btn) btn.disabled = false;
        if (label) label.textContent = 'Sign Up';
        if (spin) spin.classList.add('hidden');
        if (window.lucide) lucide.createIcons();
      }
    });
  }
});
