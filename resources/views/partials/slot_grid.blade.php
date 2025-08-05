<div class="slot-grid">
  @foreach($slots as $slot)
    @php
      // Cek apakah slot sudah lewat
      $isPast = \Carbon\Carbon::parse($slot['date'].' '.$slot['hour'].':00') < now();
      $isBooked = $slot['status'] !== __('booking.status_available');
      $slotClass = '';
      if ($isBooked) {
        $slotClass = 'slot-booked';
      } elseif ($isPast) {
        $slotClass = 'slot-past';
      }
      $disabled = ($isBooked || $isPast) ? 'disabled' : '';
      $bookstats = ($slot['units']=='') ? $slot['status'] : $slot['units'];
    @endphp
    <button class="slot {{ $slotClass }}" data-hour="{{ $slot['label'] }}" data-date="{{ $slot['date'] }}" data-hourVal="{{ $slot['hour'] }}" {{ $disabled }}>
      {{ $slot['label'] }}<br>
      <span>{{ $bookstats }}</span>
    </button>
  @endforeach
</div>