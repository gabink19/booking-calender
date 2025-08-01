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
      <h1>Pemesanan Lapangan Tenis</h1>
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
    <label class="label-tanggal">Pilih Tanggal:</label>
    <div class="section" style="overflow-x: auto; white-space: nowrap; scrollbar-width: none; -ms-overflow-style: none;">
      <div class="tanggal-list" style="display: flex; flex-direction: row; gap: 10px; white-space: nowrap; min-width: max-content;">
        @foreach($dates as $date)
        @php
            $tanggalFormatted = \Carbon\Carbon::parse($date['tanggal'])->locale('id')->isoFormat('dddd, D MMMM Y');
        @endphp
          <button 
            class="tanggal-btn{{ $loop->first ? ' selected' : '' }}" 
            data-date="{{ \Carbon\Carbon::parse($date['tanggal'])->format('d-m-Y') }}"
            onclick="document.getElementById('selected-date-string').innerHTML = 'Slot Waktu Tersedia untuk <b>{{ $tanggalFormatted }}</b>:';"
            style="min-width: 90px; white-space: normal;"
          >
            {{ $date['hari'] }}<br>
            <span>{{ \Carbon\Carbon::parse($date['tanggal'])->format('d/m') }}</span>
          </button>
        @endforeach
      </div>
    </div>
    <div class="section">
      <div class="label-slot" id="selected-date-string">
        Slot Waktu Tersedia untuk <b>
          {{
            \Carbon\Carbon::parse(
              is_array($selectedDate ?? $dates[0]) 
                ? ($selectedDate['tanggal'] ?? $dates[0]['tanggal']) 
                : ($selectedDate ?? $dates[0])
            )
            ->locale('id')
            ->isoFormat('dddd, D MMMM Y')
          }}
        </b>:
      </div>
      <div class="slot-grid">
        @foreach($slots as $slot)
          @php
            // Cek apakah slot sudah lewat
            $isPast = \Carbon\Carbon::parse($slot['date'].' '.$slot['hour'].':00') < now();
            $isBooked = $slot['status'] !== 'Tersedia';
            $slotClass = '';
            if ($isBooked) {
              $slotClass = 'slot-booked';
            } elseif ($isPast) {
              $slotClass = 'slot-past';
            }
            $disabled = ($isBooked || $isPast) ? 'disabled' : '';
            $bookstats= ($slot['units']=='') ? $slot['status'] : $slot['units'];
          @endphp
          <button class="slot {{ $slotClass }}" data-hour="{{ $slot['label'] }}" data-date="{{ $slot['date'] }}" data-hourVal="{{ $slot['hour'] }}" {{ $disabled }}>
            {{ $slot['label'] }}<br>
            <span>{ $bookstats }</span>
          </button>
        @endforeach
      </div>
    </div>
    <!-- Pengumuman floating di bawah -->
    <div class="pengumuman-floating" id="pengumumanFloating" style="
      position: fixed;
      bottom: 70px;
      background: #e8f4ff;
      border-radius: 8px;
      padding: 12px 16px;
      box-shadow: 0 2px 12px #0002;
      display: flex;
      align-items: center;
      gap: 10px;
      z-index: 9999;
      max-width: 90vw;
    ">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#4B8DDD"/><path d="M12 8v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="16" r="1" fill="#fff"/></svg>
      <span style="color:#2563a6;font-weight:500;">Notifikasi WhatsApp akan dikirim otomatis setelah booking.</span>
      <button id="closePengumuman" style="background:none;border:none;margin-left:8px;cursor:pointer;font-size:18px;color:#2563a6;" aria-label="Tutup">&times;</button>
    </div>

    <script>
      // Cek localStorage untuk pengumuman
      document.addEventListener('DOMContentLoaded', function() {
        const key = 'pengumumanFloatingClosedAt';
        const pengumuman = document.getElementById('pengumumanFloating');
        const closeBtn = document.getElementById('closePengumuman');
        const now = Date.now();
        const cache = localStorage.getItem(key);

        if (cache && now - parseInt(cache) < 3600 * 1000) {
          pengumuman.style.display = 'none';
        }

        closeBtn.addEventListener('click', function() {
          pengumuman.style.display = 'none';
          localStorage.setItem(key, Date.now());
        });
      });

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
    <button class="nav-btn active" id="nav-booking">
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
    <button class="nav-btn" id="nav-profile">
      <span class="nav-icon">
        <!-- Profile: User icon -->
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="9" r="4" fill="#4B8DDD"/><rect x="4" y="16" width="16" height="4" rx="2" fill="#4B8DDD"/></svg>
      </span>
      <span class="nav-label">Profil</span>
    </button>
  </nav>
  <!-- Tambahkan SweetAlert2 JS sebelum booking.js -->
  
  <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
  <script>
    // Inisialisasi Pusher
    var pusher = new Pusher('0eeffedf855aa275abeb', {
      cluster: 'ap1'
    });

    // Subscribe ke channel
    var channel = pusher.subscribe('booking-channel');

    // Ganti 'booking-event' sesuai dengan nama event yang dibroadcast Laravel
    
    channel.bind('booking-event', function(data) {

      // Pastikan variabel date sudah terdefinisi
      // Misal, ambil tanggal yang sedang dipilih user:
      var date = document.querySelector('.tanggal-btn.selected')?.getAttribute('data-date');
      if (!date) return;

      if (data.message === "newBooking") {
        fetch(`booking/slots?date=${encodeURIComponent(date)}`)
          .then(res => res.json())
          .then(res => {
            document.querySelector('.slot-grid').outerHTML = res.html;
          });
      }
    });

    channel.bind('pusher:subscription_succeeded', function() {
        console.log('Berhasil subscribe ke booking-channel');
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/booking.js') }}?v={{ time() }}"></script>
</body>
</html>