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

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH USER (UMUM)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| SUPER ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1'])->group(function () {

    Route::get('/super-admin/dashboard', function () {
        return view('dashboard.super_admin');
    })->name('superadmin.dashboard');

    // Vehicles
    Route::resource('vehicles', VehicleController::class);

    // Cities
    Route::resource('cities', CityController::class);

    // Meeting Points
    Route::resource('meeting-points', MeetingPointController::class);

    // Schedules
    Route::resource('schedules', DepartureScheduleController::class);

});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:2'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('dashboard.admin');
    })->name('admin.dashboard');

    // Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/expense/create', [FinanceController::class, 'createExpense'])->name('expense.create');
    Route::post('/expense/store', [FinanceController::class, 'storeExpense'])->name('expense.store');
    Route::get('/finance/report', [FinanceController::class, 'report'])->name('finance.report');

});

/*
|--------------------------------------------------------------------------
| DRIVER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:3'])->group(function () {

    Route::get('/driver/dashboard', function () {
        return view('dashboard.driver');
    })->name('driver.dashboard');

    Route::get('/driver', [DriverController::class, 'index'])->name('driver.index');
    Route::get('/driver/{id}', [DriverController::class, 'show'])->name('driver.show');

});

/*
|--------------------------------------------------------------------------
| PASSENGER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:4'])->group(function () {

    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/create/{schedule}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');

});

require __DIR__ . '/auth.php';