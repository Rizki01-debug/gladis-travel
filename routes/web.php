<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MeetingPointController;
use App\Http\Controllers\DepartureScheduleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// multirole //
Route::get('/super-admin/dashboard', function () {
    return view('dashboard.super_admin');
})->middleware('auth')->name('superadmin.dashboard');

Route::get('/admin/dashboard', function () {
    return view('dashboard.admin');
})->middleware('auth')->name('admin.dashboard');

Route::get('/driver/dashboard', function () {
    return view('dashboard.driver');
})->middleware('auth')->name('driver.dashboard');

Route::get('/booking', function () {
    return 'Halaman Booking Passenger';
})->middleware('auth')->name('booking');


Route::middleware('auth')->group(function () {

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

});


Route::middleware('auth')->group(function () {

    // Cities
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/create', [CityController::class, 'create'])->name('cities.create');
    Route::post('/cities', [CityController::class, 'store'])->name('cities.store');

    // Meeting Points
    Route::get('/meeting-points', [MeetingPointController::class, 'index'])->name('meeting_points.index');
    Route::get('/meeting-points/create', [MeetingPointController::class, 'create'])->name('meeting_points.create');
    Route::post('/meeting-points', [MeetingPointController::class, 'store'])->name('meeting_points.store');

});

Route::middleware('auth')->group(function () {

    Route::get('/schedules', [DepartureScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [DepartureScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [DepartureScheduleController::class, 'store'])->name('schedules.store');

});

require __DIR__ . '/auth.php';
