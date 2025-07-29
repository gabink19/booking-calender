<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pemesanan Lapangan Tenis</title>
  <link rel="stylesheet" href="{{ asset('css/booking.css') }}?v={{ time() }}">
  <!-- Tambahkan SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Profil Pengguna</h1>
      <p>Apartemen Bona Vista</p>
    </div>
    @if(session('success'))
      <div class="alert-success">
        <span class="alert-icon">✔️</span>
        {{ session('success') }}
      </div>
    @endif
    <div class="profile-card" style="background:#f8fafc;padding:24px 20px;border-radius:12px;box-shadow:0 2px 8px #0001;max-width:400px;margin:24px auto 32px;">
      <h2 style="margin-bottom:18px;font-size:22px;font-weight:600;color:#2563a6;">Data Profil</h2>
      <table style="width:100%;font-size:16px;">
          <tr>
              <td style="padding:6px 0;color:#555;">ID Pengguna</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->username }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">Nama</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->name }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">No. Unit</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->unit }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">WhatsApp</td>
              <td style="padding:6px 0;font-weight:500;">: {{ $user->whatsapp }}</td>
          </tr>
          <tr>
              <td style="padding:6px 0;color:#555;">Dibuat pada</td>
              <td style="padding:6px 0;font-weight:500;">: {{ \Carbon\Carbon::parse($user->created_at)->locale('id')->translatedFormat('d F Y H:i') }}</td>
          </tr>
      </table>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="text-align:center;margin-bottom:32px;">
      @csrf
      <button type="submit" style="background:#e53e3e;color:#fff;padding:10px 28px;border:none;border-radius:6px;font-size:16px;font-weight:500;cursor:pointer;box-shadow:0 1px 4px #0001;transition:background 0.2s;">
        Logout
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
    </script>
  </div>
  <nav class="bottom-nav">
    <button class="nav-btn " id="nav-booking">
      <span class="nav-icon">
        <!-- Booking: Calendar icon -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="3" fill="#4B8DDD"/><rect x="7" y="2" width="2" height="4" rx="1" fill="#fff"/><rect x="15" y="2" width="2" height="4" rx="1" fill="#fff"/><rect x="3" y="9" width="18" height="2" fill="#fff"/><rect x="7" y="13" width="2" height="2" rx="1" fill="#fff"/><rect x="11" y="13" width="2" height="2" rx="1" fill="#fff"/><rect x="15" y="13" width="2" height="2" rx="1" fill="#fff"/></svg>
      </span>
      <span class="nav-label">Booking</span>
    </button>
    <button class="nav-btn" id="nav-mybooking">
      <span class="nav-icon">
        <!-- My Booking: List/clipboard icon -->
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="3" fill="#4B8DDD"/><rect x="9" y="2" width="6" height="4" rx="2" fill="#fff"/><rect x="8" y="8" width="8" height="2" rx="1" fill="#fff"/><rect x="8" y="12" width="5" height="2" rx="1" fill="#fff"/><rect x="8" y="16" width="8" height="2" rx="1" fill="#fff"/></svg>
      </span>
      <span class="nav-label">My Booking</span>
    </button>
    <button class="nav-btn active" id="nav-profile">
      <span class="nav-icon">
        <!-- Profile: User icon -->
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="9" r="4" fill="#4B8DDD"/><rect x="4" y="16" width="16" height="4" rx="2" fill="#4B8DDD"/></svg>
      </span>
      <span class="nav-label">Profil</span>
    </button>
  </nav>
  <!-- Tambahkan SweetAlert2 JS sebelum booking.js -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/booking.js') }}?v={{ time() }}"></script>
</body>
</html>