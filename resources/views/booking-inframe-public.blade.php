<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('booking.title') }}</title>
  <link rel="stylesheet" href="{{ asset('css/booking.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <style>
.slot-grid-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.01); /* transparan, tetap blok interaksi */
    z-index: 10;
}
.section {
    position: relative;
}
</style>
</head>
<body style="background:#ffffff57 !important;">
  <div class="container" style="border: 0px;padding:50px;">
    <label class="label-tanggal">{{ __('booking.choose_date') }}</label>
    <div class="section" style="overflow-x: auto; white-space: nowrap; scrollbar-width: none; -ms-overflow-style: none;">
      <div class="tanggal-list" style="display: flex; flex-direction: row; gap: 10px; white-space: nowrap; min-width: max-content;">
        @foreach($dates as $date)
        @php
            $tanggalFormatted = \Carbon\Carbon::parse($date['tanggal'])
                ->locale(app()->getLocale())
                ->isoFormat('dddd, D MMMM Y');
            $labelAvailableSlots = __('booking.available_slots');
        @endphp
          <button 
            class="tanggal-btn{{ $loop->first ? ' selected' : '' }}" 
            data-date="{{ \Carbon\Carbon::parse($date['tanggal'])->format('d-m-Y') }}"
            onclick="document.getElementById('selected-date-string').innerHTML = '{{ $labelAvailableSlots }} <b>{{ $tanggalFormatted }}</b>:';"
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
        {{ __('booking.available_slots') }} <b>
          {{
            \Carbon\Carbon::parse(
              is_array($selectedDate ?? $dates[0]) 
                ? ($selectedDate['tanggal'] ?? $dates[0]['tanggal']) 
                : ($selectedDate ?? $dates[0])
            )
            ->locale(app()->getLocale())
            ->isoFormat('dddd, D MMMM Y')
          }}
        </b>:
      </div>
      <div class="slot-grid">
        @foreach($slots as $slot)
          @php
            $isPast = \Carbon\Carbon::parse($slot['date'].' '.$slot['hour'].':00') < now();
            $isBooked = $slot['status'] !== __('booking.status_available');
            $slotClass = '';
            if ($isBooked) {
              $slotClass = 'slot-booked';
            } elseif ($isPast) {
              $slotClass = 'slot-past';
            }
            $disabled = ($isBooked || $isPast) ? 'disabled' : '';
            $bookstats = $slot['units'];
          @endphp
          <button class="slot {{ $slotClass }}" data-hour="{{ $slot['label'] }}" data-date="{{ $slot['date'] }}" data-hourVal="{{ $slot['hour'] }}" {{ $disabled }}>
            {{ $slot['label'] }}<br>
            <span>{{ $bookstats }}</span>
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
  <script>
  window.bookingLang = {
    loading: "{{ __('booking.loading') }}",
    success: "{{ __('booking.success') }}",
    failed: "{{ __('booking.failed') }}",
    bookingTitle: "{{ __('booking.booking_title') }}",
    bookingLabel: "{{ __('booking.booking') }}",
    cancelTitle: "{{ __('booking.cancel_title') }}",
    cancelText: "{{ __('booking.cancel_text') }}",
    cancelConfirm: "{{ __('booking.cancel_confirm') }}",
    cancelCancel: "{{ __('booking.cancel_cancel') }}",
    cancelSuccess: "{{ __('booking.cancel_success') }}",
    cancelFailed: "{{ __('booking.cancel_failed') }}",
    requiredFields: "{{ __('booking.required_fields') }}",
    duration: "{{ __('booking.duration') }}",
    oneHour: "{{ __('booking.one_hour') }}",
    twoHour: "{{ __('booking.two_hour') }}",
    date: "{{ __('booking.date') }}",
    hour: "{{ __('booking.hour') }}",
    name: "{{ __('booking.name') }}",
    unit: "{{ __('booking.unit') }}",
    whatsapp: "{{ __('booking.whatsapp') }}",
    saveBooking: "{{ __('booking.save_booking') }}",
    cancelBtn: "{{ __('booking.cancel_btn') }}",
    errorGeneral: "{{ __('booking.error_general') }}",
    errorBooking: "{{ __('booking.error_booking') }}",
    errorCancel: "{{ __('booking.error_cancel') }}",
    statusMaintenance: "{{ __('booking.status_maintenance') }}",
  };
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/booking.js') }}?v={{ time() }}"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    function addSlotGridOverlay() {
        document.querySelectorAll('.slot-grid').forEach(function(grid) {
            // Cek jika overlay sudah ada
            if (!grid.parentElement.querySelector('.slot-grid-overlay')) {
                var overlay = document.createElement('div');
                overlay.className = 'slot-grid-overlay';
                grid.parentElement.appendChild(overlay);
            }
        });
    }
    addSlotGridOverlay();

    // Jika ada AJAX yang mengganti .slot-grid, pasang ulang overlay
    const observer = new MutationObserver(function(mutationsList) {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList') {
                addSlotGridOverlay();
            }
        }
    });
    document.querySelectorAll('.section').forEach(function(section) {
        observer.observe(section, { childList: true, subtree: true });
    });
});
  </script>
</body>
</html>