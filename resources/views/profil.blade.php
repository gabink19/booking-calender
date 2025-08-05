<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('profil.title') }}</title>
  <link rel="stylesheet" href="{{ asset('css/booking.css') }}?v={{ time() }}">
  <!-- Tambahkan SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <style>
    /* Responsive SweetAlert2 input */
    .swal2-popup {
      max-width: 95vw !important;
      box-sizing: border-box;
      overflow-x: hidden !important; /* Cegah scroll horizontal */
    }
    .swal2-html-container {
      overflow-x: hidden !important; /* Cegah scroll horizontal pada konten */
      word-break: break-word;
      display: flex;
      flex-direction: column;
      align-items: center; /* Center content horizontally */
    }
    .swal2-input {
      width: 50% !important;
      min-width: 0 !important;
      box-sizing: border-box;
      font-size: 1rem;
      text-align: center; /* Center text in input */
    }
    @media (max-width: 480px) {
      .swal2-popup {
        padding: 0px !important;
        width: 100% !important;
      }
      .swal2-input {
        font-size: 0.95rem;
       width: 250px !important
      }
    }
    @media (max-width: 800px) {
      .swal2-popup {
        padding: 0px !important;
        width: 100% !important;
      }
      .swal2-input {
        font-size: 0.95rem;
       width: 250px !important
      }
    }
  </style>
</head>
@php
  $backgroundImage = asset('storage/' . ($settings['app_background'] ?? 'default-background.png'));
@endphp
<body style="background: #fff url('{{ $backgroundImage }}') no-repeat center center fixed; background-size: cover;">
  <div class="container">
    <div class="header">
      <h1>{{ __('profil.header') }}</h1>
      <p>{{ __('profil.subtitle') }}</p>
      <div class="contact">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="phone-icon" viewBox="0 0 24 24" fill="none"><path d="M6.62 10.79a15.464 15.464 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.21c1.21.49 2.53.76 3.88.76a1 1 0 011 1v3.5a1 1 0 01-1 1C7.61 22.5 1.5 16.39 1.5 8.5a1 1 0 011-1H6a1 1 0 011 1c0 1.35.27 2.67.76 3.88a1 1 0 01-.21 1.11l-2.2 2.2z" fill="#fff"/></svg>
        <span>{{ __('profil.contact') }} : &nbsp; <a href="https://wa.me/62{{ preg_replace('/[^0-9]/', '', $settings['contact'] ?? '81212345678') }}" target="_blank" class="wa-link">{{ $settings['contact'] ?? '0812-1234-5678' }}</a></span>
      </div>
    </div>
    @if(session('success'))
      <div class="alert-success">
        <span class="alert-icon">✔️</span>
        {{ session('success') }}
      </div>
    @endif
    <div class="profile-card" style="background:#f8fafc;padding:24px 20px;border-radius:12px;box-shadow:0 2px 8px #0001;max-width:400px;margin:24px auto 32px;">
      <h2 style="margin-bottom:18px;font-size:22px;font-weight:600;color:#2563a6;">{{ __('profil.data_title') }}</h2>
      <table style="width:100%;font-size:16px;">
          <tr>
              <td style="padding:6px 0;color:#555;">{{ __('profil.username') }}</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->username }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">{{ __('profil.name') }}</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->name }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">{{ __('profil.unit') }}</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->unit }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">{{ __('profil.whatsapp') }}</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->whatsapp }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">{{ __('profil.created_at') }}</td>
              <td style="padding:6px 0;font-weight:500;">: {{ \Carbon\Carbon::parse($user->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i') }}</td>
          </tr>
      </table>
    </div>
    <div style="text-align:center;margin-bottom:32px;">
      <button id="ubah-password-btn" style="background:#3e76e5;color:#fff;padding:10px 28px;border:none;border-radius:6px;font-size:16px;font-weight:500;cursor:pointer;box-shadow:0 1px 4px #0001;transition:background 0.2s;">
        {{ __('profil.change_password') }}
      </button>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="text-align:center;margin-bottom:32px;">
      @csrf
      <button type="submit" style="background:#e53e3e;color:#fff;padding:10px 28px;border:none;border-radius:6px;font-size:16px;font-weight:500;cursor:pointer;box-shadow:0 1px 4px #0001;transition:background 0.2s;">
        {{ __('profil.logout') }}
      </button>
    </form>

    <script>
      // Otomatis klik tanggal hari ini jika ada
      document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const todayStr = today.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
        const btn = Array.from(document.querySelectorAll('.tanggal-btn')).find(b => b.dataset.date === todayStr);
        if (btn) btn.click();
      });

      document.querySelector('#ubah-password-btn').addEventListener('click', function(e) {
          e.preventDefault();
          Swal.fire({
              title: '{{ __("profil.change_password") }}',
              html:
                  `<form id="form-ubah-password" autocomplete="off">
                      <input type="password" id="old_password" class="swal2-input" placeholder="{{ __("profil.old_password") }}" required>
                      <input type="password" id="new_password" class="swal2-input" placeholder="{{ __("profil.new_password") }}" required>
                      <input type="password" id="confirm_password" class="swal2-input" placeholder="{{ __("profil.confirm_new_password") }}" required>
                  </form>`,
              focusConfirm: false,
              showCancelButton: true,
              confirmButtonText: '{{ __("profil.save") }}',
              cancelButtonText: '{{ __("profil.cancel") }}',
              preConfirm: () => {
                  const old_password = document.getElementById('old_password').value;
                  const new_password = document.getElementById('new_password').value;
                  const confirm_password = document.getElementById('confirm_password').value;
                  if (!old_password || !new_password || !confirm_password) {
                      Swal.showValidationMessage('{{ __("profil.required_fields") }}');
                      return false;
                  }
                  if (new_password !== confirm_password) {
                      Swal.showValidationMessage('{{ __("profil.password_not_match") }}');
                      return false;
                  }
                  return { old_password, new_password, confirm_password };
              }
          }).then((result) => {
              if (result.isConfirmed) {
                  // Kirim AJAX ke route ubah password (ganti sesuai route Anda)
                  $.ajax({
                      url: "{{ url('/update-password', ['uuid' => $user->uuid]) }}",
                      method: "POST",
                      contentType: "application/json",
                      headers: {
                          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                      },
                      data: JSON.stringify(result.value),
                      success: function(res) {
                          if (res.success) {
                              Swal.fire('{{ __("profil.success") }}', res.message, 'success');
                          } else {
                              Swal.fire('{{ __("profil.failed") }}', res.message || '{{ __("profil.failed_change") }}', 'error');
                          }
                      },
                      error: function() {
                          Swal.fire('{{ __("profil.failed") }}', '{{ __("profil.server_error") }}', 'error');
                      }
                  });
              }
          });
      });
    </script>
  </div>
  <nav class="bottom-nav">
    <button class="nav-btn " id="nav-booking">
      <span class="nav-icon">
        <!-- Booking: Calendar icon -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="3" fill="#4B8DDD"/><rect x="7" y="2" width="2" height="4" rx="1" fill="#fff"/><rect x="15" y="2" width="2" height="4" rx="1" fill="#fff"/><rect x="3" y="9" width="18" height="2" fill="#fff"/><rect x="7" y="13" width="2" height="2" rx="1" fill="#fff"/><rect x="11" y="13" width="2" height="2" rx="1" fill="#fff"/><rect x="15" y="13" width="2" height="2" rx="1" fill="#fff"/></svg>
      </span>
      <span class="nav-label">{{ __('booking.booking') }}</span>
    </button>
    <button class="nav-btn" id="nav-mybooking">
      <span class="nav-icon">
        <!-- My Booking: List/clipboard icon -->
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="3" fill="#4B8DDD"/><rect x="9" y="2" width="6" height="4" rx="2" fill="#fff"/><rect x="8" y="8" width="8" height="2" rx="1" fill="#fff"/><rect x="8" y="12" width="5" height="2" rx="1" fill="#fff"/><rect x="8" y="16" width="8" height="2" rx="1" fill="#fff"/></svg>
      </span>
      <span class="nav-label">{{ __('booking.my_booking') }}</span>
    </button>
    <button class="nav-btn active" id="nav-profile">
      <span class="nav-icon">
        <!-- Profile: User icon -->
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="9" r="4" fill="#4B8DDD"/><rect x="4" y="16" width="16" height="4" rx="2" fill="#4B8DDD"/></svg>
      </span>
      <span class="nav-label">{{ __('profil.profile') }}</span>
    </button>
  </nav>
  <!-- Tambahkan SweetAlert2 JS sebelum booking.js -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('js/booking.js') }}?v={{ time() }}"></script>
</body>
</html>