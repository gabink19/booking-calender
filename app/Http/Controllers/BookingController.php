<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use App\Notifications\BookingWhatsappNotification;
use App\Events\BookingEvent;

use function Psy\debug;

class BookingController extends Controller
{
    // Tampilkan halaman booking
    public function index(Request $request)
    {
        $dates = $this->getWeekDates();
        $selectedDate = $request->input('date', $dates[0]);
        $slots = $this->getSlots();
        $userId = session('user_id');
        $user = User::select('name', 'unit', 'whatsapp', 'is_admin')->where('uuid', $userId)->first();
        // Simpan data ke localStorage via JavaScript
        echo "<script>
            localStorage.setItem('user', JSON.stringify({
                name: " . json_encode($user->name) . ",
                unit: " . json_encode($user->unit) . ",
                whatsapp: " . json_encode($user->whatsapp) . ",
                is_admin: " . json_encode($user->is_admin) . "
            }));
        </script>";

        return view('booking', compact('dates', 'selectedDate', 'slots'));
    }

    // Proses booking
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'hour' => 'required|integer',
                'hourEnd' => 'nullable|integer|gt:hour',
                'unit' => 'required|string',
                'durationRadio' => 'required|in:1,2',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => $e->validator->errors()->first()
            ], 422);
        }
        $newDateFormat = Carbon::parse($request->date)->format('Y-m-d');
        // Validasi tidak boleh booking di tanggal & jam yang sama dengan status active (untuk unit apapun)
        $existing = Booking::where('date', $newDateFormat)
            ->where('hour', $request->hour)
            ->where('status', 'active')
            ->count();
        if ($existing>0) {
            return response()->json(['error' => 'Slot pada tanggal dan jam tersebut sudah dipesan!']);
        }
        if(!Session::has('is_admin')){
            $nowBookCount = 1;
            if ($request->hourEnd!=null) {
                $nowBookCount++;
            }
            // Cek booking per hari maksimal 2 jam untuk unit yang sama
            $unitDayBookings = Booking::where('unit', $request->unit)
                ->where('date', $newDateFormat)
                ->where('status', 'active')
                ->count();
            if ($unitDayBookings+$nowBookCount > 2) {
                return response()->json(['error' => 'Maksimal 2 jam per unit di hari yang sama dan Maksimal 4 jam per unit di minggu yang sama!']);
            }

            // Cek booking per minggu maksimal 4 jam untuk unit yang sama
            $startOfWeek = Carbon::parse($newDateFormat)->startOfWeek(Carbon::MONDAY)->toDateString();
            $endOfWeek = Carbon::parse($newDateFormat)->endOfWeek(Carbon::SUNDAY)->toDateString();
            $unitWeekBookings = Booking::where('unit', $request->unit)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->where('status', 'active')
                ->count();
            if ($unitWeekBookings >= 4) {
                return response()->json(['error' => 'Maksimal 2 jam per unit di hari yang sama dan Maksimal 4 jam per unit di minggu yang sama!']);
            }
        }
        $unit = session('unit');
        if ($unit) {
            $request->merge(['unit' => $unit]);
        }
        $booking = Booking::create([
            'date' => $newDateFormat,
            'hour' => (int)$request->hour,
            'unit' => $request->unit,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($request->hourEnd!=null) {
                $booking = Booking::create([
                    'date' => $newDateFormat,
                    'hour' => (int)$request->hourEnd,
                    'unit' => $request->unit,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }
        // Kirim notifikasi WhatsApp (gunakan Notification atau API eksternal)
        // Notification::route('whatsapp', $booking->whatsapp)
        //     ->notify(new BookingWhatsappNotification($booking));
        // event(new BookingEvent("newBooking"));
        broadcast(new BookingEvent("newBooking"))->toOthers();
        return response()->json(['success' => true, 'message' => 'Booking berhasil!']);
    }

    // Cancel booking (minimal 1 jam sebelum mulai)
    public function cancel(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $now = Carbon::now();
        $start = Carbon::parse($booking->date . ' ' . $booking->hour . ':00:00');
        if ($start->diffInMinutes($now, false) > -60) {
            return response()->json([
                'success' => false,
                'error' => 'Cancel hanya bisa 1 jam sebelum jam mulai!'
            ]);
        }
        $booking->status = 'cancelled';
        $booking->updated_at = now();
        $booking->save();
        broadcast(new BookingEvent("newBooking"))->toOthers();
        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibatalkan.'
        ]);
    }

    // Export data booking (CSV)
    public function export(Request $request)
    {
        $bookings = Booking::all();
        $filename = 'bookings-' . date('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function() use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jam Mulai', 'Jam Selesai', 'Unit', 'Nama', 'WA', 'Status']);
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->date, $b->hour_start, $b->hour_end, $b->unit, $b->name, $b->whatsapp, $b->status
                ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    // Helper: Dapatkan tanggal minggu ini (Senin-Minggu)
    /**
     * Get an array of dates for the current week starting from today.
     *
     * @return array
     */
    public function getWeekDates()
    {
        $mulai = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $tanggal = [];
        for ($i = 0; $i < 7; $i++) {
            $carbon = $mulai->copy()->addDays($i)->locale('id');
            $tanggal[] = [
                'tanggal' => $carbon->toDateString(),
                'hari' => $carbon->translatedFormat('l'), // Nama hari dalam bahasa Indonesia
            ];
        }
        return $tanggal;
    }

    // Helper: Dapatkan slot waktu per hari
    /**
     * Get an array of dates for the current week starting from today.
     *
     * @return array
     */
    public function getSlots($selectedDate = null)
    {
        $date = $selectedDate ? Carbon::parse($selectedDate)->format('Y-m-d') : Carbon::today()->toDateString();

        // Ambil semua booking pada tanggal tsb (jam & unit)
        $booked = Booking::where('date', $date)
            ->where('status', 'active')
            ->get(['hour', 'unit']);

        // Buat array jam => [unit1, unit2, ...]
        $bookedHours = [];
        foreach ($booked as $b) {
            $bookedHours[$b->hour] = $b->unit;
        }

        $slots = [];
        $unit = session('unit');
        for ($h = 6; $h < 22; $h++) {
            $isBooked = isset($bookedHours[$h]);
            $status = $isBooked ? 'Dipesan' : 'Tersedia';
            $unitsBooked = '';
            $unitsBookedH = '';
            if ($isBooked) {
                $unitsBookedH = $bookedHours[$h];
            }
            if ($status=='Dipesan') {
                $unitsBooked = $unitsBookedH;
            }
            if ($unit==$unitsBookedH) {
                $unitsBooked='';
            }
            if ($isBooked && $bookedHours[$h]) {
                $unitData = User::where('unit', $bookedHours[$h])->first();
                $unitsBooked = $unitData->is_admin ? 'Pemeliharaan' : $unitsBooked;
            }
            $slots[] = [
                'date' => $date,
                'hour' => $h,
                'label' => sprintf('%02d:00 - %02d:59', $h, $h),
                'status' => $status,
                'units' => $unitsBooked, // Tambahan: daftar unit yang booking slot ini
            ];
        }
        return $slots;
    }

    public function ajaxSlots(Request $request)
    {
        $date = $request->input('date');
        $slots = $this->getSlots($date);
        // Render slot sebagai HTML fragment
        $html = view('partials.slot_grid', compact('slots'))->render();
        return response()->json(['html' => $html]);
    }
    
    public function history(Request $request)
    {
        $unit = session('unit');
        if (!$unit) {
            return redirect('/')->with('error', 'Unit tidak ditemukan di sesi.');
        }
        $bookings = Booking::where('unit', $unit)
            ->orderBy('date', 'desc')
            ->orderBy('hour', 'desc')
            ->get();

        return view('booking-history', compact('bookings', 'unit'));
    }
    public function profil()
    {
        $userId = session('user_id');
        $user = User::select('username','name', 'unit', 'whatsapp','created_at')->where('uuid', $userId)->first();
        return view('profil', compact('user'));
    }
}
