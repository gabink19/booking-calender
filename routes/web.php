<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Mews\Captcha\Captcha;

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

// Route login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

// Group booking routes dengan middleware auth session
Route::middleware('auth.session')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('booking');
    Route::get('/booking', function () {
        return redirect()->route('booking');
    });
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/booking/cancel/{id}', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::get('/booking/export', [BookingController::class, 'export'])->name('booking.export');
    Route::get('/booking/slots', [BookingController::class, 'ajaxSlots'])->name('booking.slots');
    Route::get('/mybooking', [BookingController::class, 'history'])->name('mybooking');
    Route::get('/profil', [BookingController::class, 'profil'])->name('profil');
    Route::post('/update-password/{uuid}', [AuthController::class, 'changePass'])->name('update.password');
});


// Route login admin
Route::get('/admin/login', [AuthController::class, 'showLoginAdminForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login.submit');
Route::middleware('auth.session.admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/dashboard', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/admin/booking-inframe', [AdminController::class, 'bookingInframe'])->name('admin.booking.inframe');
    Route::get('/force', [AuthController::class, 'forceUpdate'])->name('force.update');

    Route::get('/admin/booking', [AdminController::class, 'bookingIndex'])->name('admin.booking.index');
    Route::get('/admin/booking/slots', [BookingController::class, 'ajaxSlots'])->name('admin.booking.slots');
    Route::post('/admin/booking/store', [BookingController::class, 'store'])->name('admin.booking.store');
    Route::post('/admin/booking/cancel/{id}', [BookingController::class, 'cancel'])->name('admin.booking.cancel');
    
    Route::get('/admin/user', [AdminController::class, 'userIndex'])->name('admin.user.index');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/admin/register', [AuthController::class, 'register'])->name('admin.user.create');
    Route::get('/admin/user/{uuid}', [AuthController::class, 'getUser'])->name('admin.user.get');
    Route::post('/admin/user/{uuid}/edit', [AuthController::class, 'editUser'])->name('admin.user.edit');

    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/logo', [AdminController::class, 'updateLogo'])->name('admin.settings.logo');
    Route::post('/admin/settings/background', [AdminController::class, 'updateBackground'])->name('admin.settings.background');
    Route::post('/admin/settings/info', [AdminController::class, 'updateInfo'])->name('admin.settings.info');

    Route::get('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Route captcha
Route::get('captcha/{config?}', [\Mews\Captcha\CaptchaController::class, 'getCaptcha'])->name('captcha');
Route::get('admin/captcha/{config?}', [\Mews\Captcha\CaptchaController::class, 'getCaptcha'])->name('admin.captcha');

// Route language
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['id', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
});