<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MeetingPointController;
use App\Http\Controllers\DepartureScheduleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ThemeController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));

/*
|--------------------------------------------------------------------------
| AUTH (SEMUA USER LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/super-admin', fn() => view('dashboard.super_admin'))->name('superadmin.dashboard');
        Route::get('/admin', fn() => view('dashboard.admin'))->name('admin.dashboard');
        Route::get('/driver', fn() => view('dashboard.driver'))->name('driver.dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA
    |--------------------------------------------------------------------------
    */
    Route::resources([
        'vehicles' => VehicleController::class,
        'cities' => CityController::class,
        'meeting-points' => MeetingPointController::class,
        'schedules' => DepartureScheduleController::class,
    ]);

    /*
    |--------------------------------------------------------------------------
    | BOOKING
    |--------------------------------------------------------------------------
    */
    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/create/{schedule}', [BookingController::class, 'create'])->name('create');
        Route::post('/store', [BookingController::class, 'store'])->name('store');
        Route::get('/my-booking', [BookingController::class, 'myBooking'])
            ->name('my');
    });

    /*
    |--------------------------------------------------------------------------
    | DRIVER
    |--------------------------------------------------------------------------
    */
    Route::prefix('driver')->name('driver.')->group(function () {

        Route::get('/', [DriverController::class, 'index'])->name('index');

        // 🔥 TRIP (HARUS DI ATAS)
        Route::get('/trips', [DriverController::class, 'trips'])
            ->name('trip.index');

        Route::post('/trip/{id}/complete', [DriverController::class, 'complete'])
            ->name('trip.complete');

        // 🔥 AKSI BOOKING
        Route::post('/{id}/confirm', [DriverController::class, 'confirm'])->name('confirm');
        Route::post('/{id}/reject', [DriverController::class, 'reject'])->name('reject');

        // ❗ PALING BAWAH
        Route::get('/{id}', [DriverController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | FINANCE
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {

        // ================= FINANCE =================
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');

        Route::get('/expense/create', [FinanceController::class, 'createExpense'])
            ->name('expense.create');

        Route::post('/expense/store', [FinanceController::class, 'storeExpense'])
            ->name('expense.store');

        Route::get('/finance/report', [FinanceController::class, 'report'])
            ->name('finance.report');
    });

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
    Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');
});

Route::get('/test-driver', function () {
    return route('driver.trip.index');
});

require __DIR__ . '/auth.php';
