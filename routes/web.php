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
    | DASHBOARD (ROLE BASED REDIRECT)
    |--------------------------------------------------------------------------
    */
    Route::get('/super-admin/dashboard', fn() => view('dashboard.super_admin'))->name('superadmin.dashboard');
    Route::get('/admin/dashboard', fn() => view('dashboard.admin'))->name('admin.dashboard');
    Route::get('/driver/dashboard', fn() => view('dashboard.driver'))->name('driver.dashboard');

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA (DIHANDLE DI CONTROLLER 🔥)
    |--------------------------------------------------------------------------
    */
    Route::resource('vehicles', VehicleController::class);
    Route::resource('cities', CityController::class);
    Route::resource('meeting-points', MeetingPointController::class);
    Route::resource('schedules', DepartureScheduleController::class);

    /*
    |--------------------------------------------------------------------------
    | BOOKING (PASSENGER)
    |--------------------------------------------------------------------------
    */
    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/create/{schedule}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');

    /*
    |--------------------------------------------------------------------------
    | DRIVER
    |--------------------------------------------------------------------------
    */
    Route::get('/driver', [DriverController::class, 'index'])->name('driver.index');
    Route::get('/driver/{id}', [DriverController::class, 'show'])->name('driver.show');

    /*
    |--------------------------------------------------------------------------
    | FINANCE (ADMIN + SUPER ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/expense/create', [FinanceController::class, 'createExpense'])->name('expense.create');
    Route::post('/expense/store', [FinanceController::class, 'storeExpense'])->name('expense.store');
    Route::get('/finance/report', [FinanceController::class, 'report'])->name('finance.report');

    /*
    |--------------------------------------------------------------------------
    | SETTINGS + THEME
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
    Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');
});

require __DIR__ . '/auth.php';