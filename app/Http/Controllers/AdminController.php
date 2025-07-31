<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

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
        $bookings = \App\Models\Booking::select('bookings.*', 'users.name', 'users.is_admin')
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

    // Tampilkan halaman booking
    public function bookingInframe(Request $request)
    {
        $dates = app(\App\Http\Controllers\BookingController::class)->getWeekDates();
        $selectedDate = $request->input('date', $dates[0]);
        $slots = app(\App\Http\Controllers\BookingController::class)->getSlots();

        $userId = session('user_id');
        $user = User::select('name', 'unit', 'whatsapp', 'is_admin')->where('uuid', $userId)->first();
        // Simpan data ke localStorage via JavaScript
        echo "<script>
            localStorage.setItem('user', JSON.stringify({
                name: " . json_encode($user->name) . ",
                unit: " . json_encode($user->unit) . ",
                is_admin: " . json_encode($user->is_admin) . ",
                whatsapp: " . json_encode($user->whatsapp) . "
            }));
        </script>";

        return view('admin.booking-inframe', compact('dates', 'selectedDate', 'slots'));
    }

    public function settings()
    {
        $settings = Setting::all();
        $settArr = [];
        foreach ($settings as $setting) {
            $settArr[$setting->key_name] = $setting->value;
        }
        $settings = $settArr;
        return view('admin.settings', compact('settings'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
        ]);

        // Simpan file logo baru
        $logoPath = $request->file('logo')->store('images', 'public');

        // Simpan path logo ke database (misal: tabel settings)
        DB::table('settings')->where('key_name', 'app_logo')->update(['value' => $logoPath]);

        return redirect()->back()->with('success', 'Logo berhasil diubah!');
    }

    public function updateBackground(Request $request)
    {
        $request->validate([
            'background' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
        ]);

        // Simpan file background baru
        $backgroundPath = $request->file('background')->store('images', 'public');

        // Simpan path background ke database (misal: tabel settings)
        DB::table('settings')->where('key_name', 'app_background')->update(['value' => $backgroundPath]);

        return redirect()->back()->with('success', 'Background berhasil diubah!');
    }

    public function updateInfo(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        // Simpan informasi aplikasi ke database (misal: tabel settings)
        DB::table('settings')->where('key_name', $request->input('key'))->update(['value' => $request->input('value')]);

        return redirect()->back()->with('success', 'Informasi aplikasi berhasil diubah!');
    }
}
