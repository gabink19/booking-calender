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
@php
  $backgroundImage = asset('storage/' . ($settings['app_background'] ?? 'default-background.png'));
@endphp
<body style="background: #fff url('{{ $backgroundImage }}') no-repeat center center fixed; background-size: cover;">
  <div class="container">
    <div class="header">
      <h1>Riwayat Pemesanan Lapangan Tenis</h1>
      <p>Apartemen Bona Vista</p>
      <div class="contact">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="phone-icon" viewBox="0 0 24 24" fill="none"><path d="M6.62 10.79a15.464 15.464 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.21c1.21.49 2.53.76 3.88.76a1 1 0 011 1v3.5a1 1 0 01-1 1C7.61 22.5 1.5 16.39 1.5 8.5a1 1 0 011-1H6a1 1 0 011 1c0 1.35.27 2.67.76 3.88a1 1 0 01-.21 1.11l-2.2 2.2z" fill="#fff"/></svg>
        <span>WhatsApp : &nbsp; <a href="https://wa.me/62{{ preg_replace('/[^0-9]/', '', $settings['contact'] ?? '81212345678') }}" target="_blank" class="wa-link">{{ $settings['contact'] ?? '0812-1234-5678' }}</a></span>
      </div>
    </div>
    @if(session('success'))
      <div class="alert-success">
        <span class="alert-icon">✔️</span>
        {{ session('success') }}
      </div>
    @endif
    <div class="section">
      <div class="slot-grid" style="display: block !important;">
        @if(count($bookings) === 0)
          <div style="text-align:center;color:#888;padding:40px 0 30px 0;font-size:18px;">
            Anda belum pernah melakukan pemesanan.
          </div>
        @else
          @foreach($bookings as $booking)
            @php
              // Cek apakah slot sudah lewat
              $isPast = \Carbon\Carbon::parse($booking->date.' '.$booking->hour.':00') < now();
              $isBooked = $booking->status == 'cancelled';
              $slotClass = '';
              $status = "Aktif";
              if ($isBooked) {
                $slotClass = 'slot-booked';
                $status = "Dibatalkan";
              } elseif ($isPast) {
                $slotClass = 'slot-past';
                $status = "Lewat";
              }
              $disabled = $isPast ? 'disabled' : '';
            @endphp
            <button style="text-align: justify;padding: 12px 16px;width:100% !important;margin-bottom:10px;cursor:default;" class="slot {{ $slotClass }}" data-hour="{{ $booking->hour }}" data-date="{{ $booking->date }}" data-hourVal="{{ $booking->hour }}" {{ $disabled }}>
              Tanggal Pemesanan: {{ \Carbon\Carbon::parse($booking->date)->locale('id')->translatedFormat('d F Y') }} {{ $booking->hour }}:00 - {{ $booking->hour }}:59<br>
              <span class="span-{{ $slotClass }}" style="float:left;">Status: <b>{{ $status }}</b></span>
              @if($status == "Aktif" && \Carbon\Carbon::parse($booking->date.' '.$booking->hour.':00')->diffInMinutes(now(), false) <= -60)
                <span style="float:right;">
                  <span style="display:inline-block;">
                    <span style="margin-left:10px;">
                      <span class="btn-cancel-booking" data-bookingid="{{ $booking->id }}" data-datebook="{{ \Carbon\Carbon::parse($booking->date)->locale('id')->translatedFormat('d F Y') }} {{ $booking->hour }}:00 - {{ $booking->hour }}:59" style="background:#e74c3c;color:#fff;padding:7px 18px;border-radius:6px;cursor:pointer;font-size:15px;font-weight:500;">Batalkan Pemesanan</span>
                    </span>
                  </span>
                </span>
              @endif
            </button>
          @endforeach
        @endif
      </div>
    </div>

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
    <button class="nav-btn active" id="nav-mybooking">
      <span class="nav-icon">
        <!-- My Booking: List/clipboard icon -->
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="3" fill="#4B8DDD"/><rect x="9" y="2" width="6" height="4" rx="2" fill="#fff"/><rect x="8" y="8" width="8" height="2" rx="1" fill="#fff"/><rect x="8" y="12" width="5" height="2" rx="1" fill="#fff"/><rect x="8" y="16" width="8" height="2" rx="1" fill="#fff"/></svg>
      </span>
      <span class="nav-label">My Booking</span>
    </button>
    <button class="nav-btn" id="nav-profile">
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