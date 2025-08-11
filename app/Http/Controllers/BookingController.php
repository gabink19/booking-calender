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
use App\Models\SendNotif;
use Illuminate\Support\Str;

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
                is_admin: " . $user->is_admin . "
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
            return response()->json(['error' => __('booking.slot_already_booked')]);
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
                return response()->json(['error' => __('booking.max_per_day_week')]);
            }

            // Cek booking per minggu maksimal 4 jam untuk unit yang sama
            $startOfWeek = Carbon::parse($newDateFormat)->startOfWeek(Carbon::MONDAY)->toDateString();
            $endOfWeek = Carbon::parse($newDateFormat)->endOfWeek(Carbon::SUNDAY)->toDateString();
            $unitWeekBookings = Booking::where('unit', $request->unit)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->where('status', 'active')
                ->count();
            if ($unitWeekBookings >= 4) {
                return response()->json(['error' => __('booking.max_per_day_week')]);
            }
        }
        $unit = session('unit');
        if ($unit) {
            $request->merge(['unit' => $unit]);
        }
        Booking::create([
            'date' => $newDateFormat,
            'hour' => (int)$request->hour,
            'unit' => $request->unit,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($request->hourEnd!=null) {
                Booking::create([
                    'date' => $newDateFormat,
                    'hour' => (int)$request->hourEnd,
                    'unit' => $request->unit,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }
        broadcast(new BookingEvent("newBooking"))->toOthers();
        $notifId = (string) Str::uuid();
        if (!Session::has('is_admin')) {
            $userId = session('user_id');
            $user = User::select('name', 'unit', 'whatsapp')->where('uuid', $userId)->first();
            $bookingData = [
                'duration' => $request->durationRadio == '2' ? '2 Jam' : '1 Jam',
                'date' => Carbon::parse($newDateFormat)->locale(app()->getLocale())->translatedFormat('l d F Y'),
                'hour' => $request->hour,
                'hourEnd' => $request->hourEnd ?? null,
                'name' => $user->name,
                'unit' => $user->unit,
                'whatsapp' => $user->whatsapp,
            ];

            $message = $this->formatBookingMessage($bookingData);

            // Insert ke send_notif
            SendNotif::create([
                'id' => $notifId,
                'messages' => $message,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('booking.booking_success'),
            'notifId' => $notifId
        ]);
    }

    // Cancel booking (minimal 1 jam sebelum mulai)
    public function cancel(Request $request, $id)
    {
        $booking = Booking::with('user')->findOrFail($id);
        $now = Carbon::now();
        $start = Carbon::parse($booking->date . ' ' . $booking->hour . ':00:00');
        if ($start->diffInMinutes($now, false) > -60) {
            return response()->json([
                'success' => false,
                'error' => __('booking.cancel_time_error')
            ]);
        }
        $booking->status = 'cancelled';
        $booking->updated_at = now();
        $booking->save();
        broadcast(new BookingEvent("newBooking"))->toOthers();
        $notifId = (string) Str::uuid();
        if (!Session::has('is_admin')) {
            $bookingData = [
                'duration' => $request->durationRadio == '2' ? '2 Jam' : '1 Jam',
                'date' => Carbon::parse($booking->date)->locale(app()->getLocale())->translatedFormat('l d F Y'),
                'hour' => $booking->hour,
                'hourEnd' => $booking->hourEnd ?? null,
                'name' => $booking->user->name,
                'unit' => $booking->user->unit,
                'whatsapp' => $booking->user->whatsapp,
            ];
            $message = $this->formatCancelMessage($bookingData);
            // Insert ke send_notif
            SendNotif::create([
                'id' => $notifId,
                'messages' => $message,
                'user_id' => $booking->user->uuid,
                'created_at' => now(),
                'updated_at' => null,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => __('booking.cancel_success'),
            'notifId' => $notifId
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
                    $b->date, $b->hour_start, $b->hour_end, $b->unit, $b->name, $b->whatsapp, __($b->status == 'active' ? 'booking.status_active' : 'booking.status_cancelled')
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
        $locale = app()->getLocale(); // ambil locale dari aplikasi
        for ($i = 0; $i < 7; $i++) {
            $carbon = $mulai->copy()->addDays($i)->locale($locale);
            $tanggal[] = [
                'tanggal' => $carbon->toDateString(),
                'hari' => $carbon->translatedFormat('l'), // Nama hari sesuai bahasa
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
        for ($h = 6; $h < 21; $h++) {
            $isBooked = isset($bookedHours[$h]);
            $status = $isBooked ? __('booking.status_booked') : __('booking.status_available');
            $unitsBooked = '';
            $unitsBookedH = '';
            if ($isBooked) {
                $unitsBookedH = $bookedHours[$h];
            }
            if ($status == __('booking.status_booked')) {
                $unitsBooked = $unitsBookedH;
            }
            if ($unit == $unitsBookedH) {
                $unitsBooked = '';
            }
            if ($isBooked && $bookedHours[$h]) {
                $unitData = User::where('unit', $bookedHours[$h])->first();
                $unitsBooked = $unitData->is_admin ? __('booking.status_maintenance') : $unitsBooked;
            }
            $slots[] = [
                'date' => $date,
                'hour' => $h,
                'label' => sprintf('%02d:00 - %02d:00', $h, $h+1),
                'status' => $status,
                'units' => $unitsBooked,
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
            return redirect('/')->with('error', __('booking.unit_not_found'));
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
        $user = User::select('uuid','username','name', 'unit', 'whatsapp','created_at')->where('uuid', $userId)->first();
        return view('profil', compact('user'));
    }

    public function sendNotification(Request $request, $id)
    {
        
        $getNotif = SendNotif::with('user')->findOrFail($id);
        $noWa = $getNotif->user ? $getNotif->user->whatsapp : null;
        $message = $getNotif->messages;

        // Ubah awalan 0 menjadi 62 jika perlu
        if (substr($noWa, 0, 1) === '0') {
            $noWa = '62' . substr($noWa, 1);
        }

        // Validasi nomor WhatsApp
        if (!$noWa || !preg_match('/^\+?[0-9]{10,15}$/', $noWa)) {
            return response()->json(['error' => __('booking.invalid_whatsapp')], 422);
        }
        // Kirim notifikasi WhatsApp via API eksternal
        // Logging request data
        \Log::info('WA Request', [
            'url' => 'https://app.saungwa.com/api/create-message',
            'payload' => [
            'appkey' => config('services.saungwa.appkey'),
            'authkey' => config('services.saungwa.authkey'),
            'to' => $noWa,
            'message' => $message,
            'sandbox' => 'false'
            ]
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app.saungwa.com/api/create-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
            'appkey' => config('services.saungwa.appkey'),
            'authkey' => config('services.saungwa.authkey'),
            'to' => $noWa,
            'message' => $message,
            'sandbox' => 'false'
            ),
        ));
        $response = curl_exec($curl);

        // Logging response data
        \Log::info('WA Response', [
            'response' => $response,
            'curl_error' => curl_error($curl),
            'curl_info' => curl_getinfo($curl)
        ]);
        curl_close($curl);
        $status = 'failed';
        if ($response) {
            $decoded = json_decode($response, true);
            if (is_array($decoded) && isset($decoded['status']) && ($decoded['status'] == 200 || $decoded['status'] === true)) {
            $status = 'sent';
            }
        }
        SendNotif::where('id', $id)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => __('booking.notification_sent'), 'api_response' => $response]);
    }

    private function formatBookingMessage($bookingData)
    {
        $unit = $bookingData['unit'] ?? '-';
        $date = $bookingData['date'] ?? '-';

        // Jam booking
        if (!empty($bookingData['hourEnd'])) {
            $startHour = (int)($bookingData['hourStart'] ?? $bookingData['hour']);
            $endHour = (int)$bookingData['hourEnd'];
            $jam = sprintf("%02d:00 - %02d:00", $startHour, $endHour + 1);
        } else {
            $hour = $bookingData['hour'] ?? '-';
            if (is_numeric($hour)) {
                $jam = sprintf("%02d:00 - %02d:00", $hour, $hour + 1);
            } else {
                $jam = $hour . " - " . $hour;
            }
        }

        $court = $bookingData['court'] ?? '';

        // Gunakan helper __() dengan parameter
        $message = __("booking.wa_greeting", ['unit' => $unit]) . "\n";
        $message .= __("booking.wa_thanks") . "\n";
        $message .= __("booking.wa_court", ['court' => $court]) . "Tennis\n";
        $message .= __("booking.wa_date", ['date' => $date]) . "\n";
        $message .= __("booking.wa_time", ['time' => $jam]) . "\n\n";
        $message .= __("booking.wa_footer");

        return $message;
    }

    private function formatCancelMessage($bookingData)
    {
        $unit = $bookingData['unit'] ?? '-';
        $date = $bookingData['date'] ?? '-';

        // Jam booking
        if (!empty($bookingData['hourEnd'])) {
            $startHour = (int)($bookingData['hourStart'] ?? $bookingData['hour']);
            $endHour = (int)$bookingData['hourEnd'];
            $jam = sprintf("%02d:00 - %02d:00", $startHour, $endHour + 1);
        } else {
            $hour = $bookingData['hour'] ?? '-';
            if (is_numeric($hour)) {
                $jam = sprintf("%02d:00 - %02d:00", $hour, $hour + 1);
            } else {
                $jam = $hour . " - " . $hour;
            }
        }

        $message = __("booking.wa_cancel_greeting", ['unit' => $unit]) . "\n";
        $message .= __("booking.wa_cancel_info") . "\n";
        $message .= "📅 " . $date . "\n";
        $message .= "⏰ " . $jam . "\n";
        $message .= __("booking.wa_cancel_status") . "\n\n";
        $message .= __("booking.wa_cancel_instruction") . "\n\n";
        $message .= __("booking.wa_cancel_footer");

        return $message;
    }
    private function formatReminderMessage($bookingData)
    {
        $unit = $bookingData['unit'] ?? '-';
        $date = $bookingData['date'] ?? '-';

        // Jam booking
        if (!empty($bookingData['hourEnd'])) {
            $startHour = (int)($bookingData['hourStart'] ?? $bookingData['hour']);
            $endHour = (int)$bookingData['hourEnd'];
            $jam = sprintf("%02d:00 - %02d:00", $startHour, $endHour + 1);
        } else {
            $hour = $bookingData['hour'] ?? '-';
            if (is_numeric($hour)) {
                $jam = sprintf("%02d:00 - %02d:00", $hour, $hour + 1);
            } else {
                $jam = $hour . " - " . $hour;
            }
        }

        $message = __("booking.wa_reminder_greeting", ['unit' => $unit]) . "\n";
        $message .= __("booking.wa_reminder_info") . "\n";
        $message .= __("booking.wa_reminder_date", ['date' => $date]) . "\n";
        $message .= __("booking.wa_reminder_time", ['time' => $jam]) . "\n";
        $message .= __("booking.wa_reminder_status") . "\n\n";
        $message .= __("booking.wa_reminder_footer");

        return $message;
    }
}
