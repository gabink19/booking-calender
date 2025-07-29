<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [BookingController::class, 'index'])->name('booking');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::post('/booking/cancel/{id}', [BookingController::class, 'cancel'])->name('booking.cancel');
Route::get('/booking/export', [BookingController::class, 'export'])->name('booking.export');
Route::get('/booking/slots', [BookingController::class, 'ajaxSlots'])->name('booking.slots');