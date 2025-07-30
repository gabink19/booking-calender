@extends('admin.layouts.app')

@section('content')
<div class="card summary">
    <h2>Ringkasan</h2>
    <div class="summary-list">
        <div>
            <span class="sum-title">Booking Hari Ini</span> 
            <span class="sum-value" id="today-booking">{{ $todayBookings }}</span>
        </div>
        <div>
            <span class="sum-title">Tersedia Hari Ini</span>
            <span class="sum-value" id="available-slot">{{ $availableSlot ?? 0 }}</span>
        </div>
        <div>
            <span class="sum-title">Tersedia Minggu Ini</span>
            <span class="sum-value" id="available-slot-week">{{ $availableSlotWeek ?? 0 }}</span>
        </div>
        <div>
            <span class="sum-title">Booking Dibatalkan Hari Ini</span>
            <span class="sum-value" id="cancelled-booking">{{ $cancelledBooking ?? 0 }}</span>
        </div>
    </div>
</div>
@endsection