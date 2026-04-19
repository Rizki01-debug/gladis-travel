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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;

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
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {

        Route::get('/super-admin', [DashboardController::class, 'superAdmin'])
            ->middleware('role:1')
            ->name('superadmin.dashboard');

        Route::get('/admin', [DashboardController::class, 'admin'])
            ->middleware('role:2')
            ->name('admin.dashboard');

        Route::get('/driver', [DashboardController::class, 'driver'])
            ->middleware('role:3')
            ->name('driver.dashboard');

        Route::get('/dashboard', [DashboardController::class, 'user'])
            ->name('dashboard.user');
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
    | USER MANAGEMENT (SUPER ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class)
        ->middleware('role:1');

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA (FEATURE BASED)
    |--------------------------------------------------------------------------
    */
    Route::resource('vehicles', VehicleController::class)->middleware('feature:vehicles');
    Route::resource('cities', CityController::class)->middleware('feature:cities');
    Route::resource('meeting-points', MeetingPointController::class)->middleware('feature:meeting_points');
    Route::resource('schedules', DepartureScheduleController::class)->middleware('feature:schedules');
    Route::resource('tariffs', TariffController::class)->middleware('feature:tariffs');

    /*
    |--------------------------------------------------------------------------
    | FEATURE MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::prefix('features')
        ->middleware('role:1')
        ->name('features.')
        ->group(function () {

            Route::get('/', [FeatureController::class, 'index'])->name('index');
            Route::post('/toggle', [FeatureController::class, 'toggle'])->name('toggle');
        });

    /*
    |--------------------------------------------------------------------------
    | BOOKING (PASSENGER)
    |--------------------------------------------------------------------------
    */
    Route::prefix('booking')
        ->middleware('feature:booking')
        ->name('booking.')
        ->group(function () {

            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::get('/create/{schedule}', [BookingController::class, 'create'])->name('create');
            Route::post('/store', [BookingController::class, 'store'])->name('store');
            Route::get('/my-booking', [BookingController::class, 'myBooking'])->name('my');

            // 🔥 FINAL: pakai DELETE (sudah sesuai blade)
            Route::delete('/{id}/cancel', [BookingController::class, 'cancel'])
                ->name('cancel');
        });

    /*
    |--------------------------------------------------------------------------
    | DRIVER
    |--------------------------------------------------------------------------
    */
    Route::prefix('driver')
        ->middleware('feature:driver')
        ->name('driver.')
        ->group(function () {

            Route::get('/', [DriverController::class, 'index'])->name('index');
            Route::get('/trips', [DriverController::class, 'trips'])->name('trips');
            Route::get('/earnings', [DriverController::class, 'earnings'])->name('earnings');

            Route::post('/trip/{id}/start', [DriverController::class, 'start'])->name('trip.start');
            Route::post('/trip/{id}/complete', [DriverController::class, 'complete'])->name('trip.complete');

            Route::post('/{id}/confirm', [DriverController::class, 'confirm'])->name('confirm');
            Route::post('/{id}/reject', [DriverController::class, 'reject'])->name('reject');

            // 🔥 HARUS PALING BAWAH
            Route::get('/{id}', [DriverController::class, 'show'])->name('show');
        });

    /*
    |--------------------------------------------------------------------------
    | FINANCE
    |--------------------------------------------------------------------------
    */
    Route::prefix('finance')->name('finance.')->group(function () {

        Route::get('/', [FinanceController::class, 'index'])
            ->middleware('feature:finance')
            ->name('index');

        Route::get('/expense/create', [FinanceController::class, 'createExpense'])
            ->middleware('feature:finance')
            ->name('expense.create');

        Route::post('/expense/store', [FinanceController::class, 'storeExpense'])
            ->middleware('feature:finance')
            ->name('expense.store');

        Route::get('/expense/{id}/edit', [FinanceController::class, 'editExpense'])
            ->middleware('feature:finance')
            ->name('expense.edit');

        Route::put('/expense/{id}/update', [FinanceController::class, 'updateExpense'])
            ->middleware('feature:finance')
            ->name('expense.update');

        Route::delete('/expense/{id}/delete', [FinanceController::class, 'deleteExpense'])
            ->middleware('feature:finance')
            ->name('expense.delete');

        Route::get('/report', [FinanceController::class, 'report'])
            ->middleware('feature:laporan')
            ->name('report');

        Route::get('/export-pdf', [FinanceController::class, 'exportPdf'])
            ->middleware('feature:laporan')
            ->name('export.pdf');

        Route::get('/setoran', [FinanceController::class, 'setoran'])
            ->middleware('feature:finance')
            ->name('setoran');

        Route::post('/setoran/{id}/confirm', [FinanceController::class, 'confirmSetoran'])
            ->middleware('feature:finance')
            ->name('setoran.confirm');
    });

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
    Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');
});

/*
|--------------------------------------------------------------------------
| ACTIVITY LOG (SUPER ADMIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1', 'feature:activity_logs'])
    ->prefix('activity')
    ->name('activity.')
    ->group(function () {

        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    });

/*
|--------------------------------------------------------------------------
| TEST
|--------------------------------------------------------------------------
*/
Route::get('/test-driver', fn() => route('driver.index'));

/*
|--------------------------------------------------------------------------
| AUTH (DEFAULT)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
