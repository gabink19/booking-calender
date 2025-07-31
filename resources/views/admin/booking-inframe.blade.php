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
<body style="background:#fff !important">
  <div class="container" style="border: 0px;padding:5px;">
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