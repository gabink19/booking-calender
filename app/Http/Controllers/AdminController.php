<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Booking hari ini
        $today = now()->toDateString();
        $todayBookings = \App\Models\Booking::where('date', $today)
            ->where('status', 'active')
            ->count();
        // Slot tersedia hari ini (misal: total slot 12 per hari)
        $totalSlot = 12;
        $bookedSlot = \App\Models\Booking::where('date', $today)
            ->where('status', 'active')
            ->count();
        $availableSlot = max($totalSlot - $bookedSlot, 0);

        // Slot tersedia minggu ini (misal: total slot 12 per hari, 7 hari)
        $startOfWeek = now()->addDay()->toDateString(); // mulai hari besok
        $endOfWeek = now()->endOfWeek()->toDateString();
        $totalSlotPerDay = 12;

        // Hitung jumlah hari dari hari ini sampai akhir minggu
        $days = now()->addDay()->diffInDays(now()->endOfWeek());
        $totalSlotWeek = $totalSlotPerDay * $days;

        $bookedSlotWeek = \App\Models\Booking::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', 'active')
            ->count();

        $availableSlotWeek = max($totalSlotWeek - $bookedSlotWeek, 0)+$availableSlot;

        // Booking dibatalkan hari ini
        $cancelledBooking = \App\Models\Booking::where('date', $today)
            ->where('status', 'cancelled')
            ->count();

        return view('admin.dashboard', compact(
            'availableSlot',
            'availableSlotWeek',
            'cancelledBooking',
            'todayBookings'
        ));
    }

    public function bookingIndex()
    {
        $bookings = \App\Models\Booking::select('bookings.*', 'users.name')
            ->leftJoin('users', 'bookings.unit', '=', 'users.unit')
            ->distinct()
            ->orderBy('date', 'desc')
            ->orderBy('hour', 'desc')
            ->get();

        return view('admin.booking',compact('bookings'));
    }

    public function userIndex()
    {
        $users = \App\Models\User::orderBy('unit')->get();
        return view('admin.user', compact('users'));
    }
}
