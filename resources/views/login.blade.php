<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Pengguna - Apartemen Bona Vista</title>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ time() }}">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- Tambahkan di dalam <head> atau sebelum </body> -->
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="container" style="position:relative;">
    <div class="language-switcher" style="position:absolute; top:18px; right:24px; z-index:10;">
      <form method="get" action="" id="langForm">
        <select name="lang" id="langSelect" onchange="changeLang()" style="padding:4px 8px; border-radius:4px;">
          <option value="id" {{ app()->getLocale() == 'id' ? 'selected' : '' }}>ID</option>
          <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>EN</option>
        </select>
      </form>
      <script>
        function changeLang() {
          var locale = document.getElementById('langSelect').value;
          window.location.href = 'lang/' + locale;
        }
      </script>
    </div>
    <div class="login-header" style="display:flex;flex-direction:column;align-items:center;justify-content:center;margin-bottom:18px;">
      <div class="login-logo" style="margin-bottom:8px;">
        <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Logo Apartemen Bona Vista" style="height:120px;width:auto;display:block;margin:0 auto;">
      </div>
      <div>
        <h1 class="title" style="margin:0;font-size:1.5em;line-height:1.2;text-align:center;">
          <span>{{ __('auth.login_title') }}</span>
        </h1>
        <div class="subtitle" style="font-size:1em;color:#6366f1;margin-top:2px;text-align:center;">
          {{ __('auth.login_subtitle') }}
        </div>
      </div>
    </div>
    <div class="desc" style="text-align:center;">
      {{ __('auth.login_desc') }}
    </div>
    <form id="loginForm" method="POST" action="{{ route('login.submit') }}" autocomplete="off">
      @csrf
      <label for="username" class="form-label">{{ __('auth.username') }}</label>
      <input type="text" id="username" name="username"  class="form-input" required/>
      <label for="password" class="form-label">{{ __('auth.password') }}</label>
      <input type="password" id="password" name="password" class="form-input" required/>

      <div style="margin-bottom:8px; display:flex; justify-content:center;">
        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
      </div>
      <button type="submit" class="btn-login">{{ __('auth.login_btn') }}</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Refresh captcha
    function refreshCaptcha() {
      document.getElementById('captcha-img').src = 'captcha/flat?' + Date.now();
    }

    // Validasi frontend sebelum submit
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value.trim();
      const captcha = document.getElementById('captcha').value.trim();
      if (!username || !password || !captcha) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: "{{ __('auth.login_failed') }}",
          text: "{{ __('auth.fill_all') }}",
          confirmButtonColor: '#6366f1'
        });
      }
    });
  </script>

  @if ($errors->any())
    <script>
      let errorMsg = {!! json_encode(implode("\n", $errors->all())) !!};
      Swal.fire({
        icon: 'error',
        title: "{{ __('auth.login_failed') }}",
        text: errorMsg,
        confirmButtonColor: '#6366f1'
      });
    </script>
  @endif
</body>
</html>