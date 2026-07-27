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
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\WebSettingController;
use App\Http\Controllers\PassengerProfileController;
use App\Http\Controllers\PaymentController; // 🔥 TAMBAHKAN INI

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

// 🔥 Landing Page CMS (ganti welcome default)
Route::get('/', [LandingController::class, 'index'])
    ->name('landing');

// 🔥 Preview Landing (GET → tampil iframe)
Route::get('/preview', [LandingController::class, 'preview'])
    ->name('landing.preview');

// 🔥 Store Preview (POST → kirim data sementara)
Route::post('/preview', [LandingController::class, 'storePreview'])
    ->name('landing.preview.store');

/*
|--------------------------------------------------------------------------
| AUTH USER (PASSENGER FLOW)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // ================= REDIRECT =================
    Route::get('/booking', function () {
        return redirect()->route('dashboard.user');
    })->name('booking.redirect');


    // ================= PROFILE (PASSENGER) =================
    Route::prefix('passenger/profile')
        ->name('passenger.profile.')
        ->group(function () {

            // 🔥 halaman profile
            Route::get('/', [PassengerProfileController::class, 'edit'])
                ->name('index');

            // 🔥 update profile
            Route::post('/', [PassengerProfileController::class, 'update'])
                ->name('update');
        });

});

/*
|--------------------------------------------------------------------------
| ADMIN (SUPER ADMIN ONLY)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'role:1'])
    ->name('admin.')
    ->group(function () {

        // ================= CMS SECTION =================
        Route::resource('sections', SectionController::class);

        Route::patch('sections/{section}/toggle', [SectionController::class, 'toggle'])
            ->name('sections.toggle');

        // ================= WEB SETTINGS =================
        Route::get('settings', [WebSettingController::class, 'edit'])
            ->name('settings.edit');

        Route::post('settings', [WebSettingController::class, 'update'])
            ->name('settings.update');
    });

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
            ->middleware('role:4')
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
        ->middleware(['auth', 'role:1']) // 🔥 wajib login + hanya super admin
        ->name('features.')
        ->group(function () {

            // 🔥 halaman pengaturan fitur
            Route::get('/', [FeatureController::class, 'index'])
                ->name('index');

            // 🔥 update semua fitur (bulk)
            Route::post('/update', [FeatureController::class, 'bulkUpdate'])
                ->name('bulkUpdate');
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
    | 🔥 PAYMENT (TAMBAHKAN INI)
    |--------------------------------------------------------------------------
    */
    Route::prefix('payment')
        ->middleware('feature:booking') // Menggunakan fitur booking karena terkait
        ->name('payment.')
        ->group(function () {

            // Halaman pilih metode pembayaran
            Route::get('/{booking}', [PaymentController::class, 'index'])
                ->name('index');

            // Generate Snap Token Midtrans
            Route::post('/snap/{booking}', [PaymentController::class, 'createSnap'])
                ->name('snap');

            // Callback dari Midtrans (tanpa auth)
            Route::post('/callback', [PaymentController::class, 'callback'])
                ->name('callback')
                ->withoutMiddleware(['auth']); // 🔥 Penting: callback tanpa auth

            // Halaman sukses
            Route::get('/success', [PaymentController::class, 'success'])
                ->name('success');

            // Halaman gagal
            Route::get('/failed', [PaymentController::class, 'failed'])
                ->name('failed');

            // Cek status payment (API)
            Route::get('/status/{orderId}', [PaymentController::class, 'checkStatus'])
                ->name('status');

            // Cancel/Expire payment
            Route::post('/cancel/{orderId}', [PaymentController::class, 'cancel'])
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
            
            /* Pengembangan Selanjutnya */
            // Route::post('/{id}/reject', [DriverController::class, 'reject'])->name('reject');

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

/*
|--------------------------------------------------------------------------
| TEST MIDTRANS
|--------------------------------------------------------------------------
*/
Route::get('/test-midtrans', function () {
    try {
        $service = new App\Services\MidtransService();
        
        // Test data
        $testData = [
            'order_id' => 'TEST-' . time(),
            'gross_amount' => 10000,
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '081234567890',
            'items' => [
                [
                    'id' => 'TEST-ITEM',
                    'price' => 10000,
                    'quantity' => 1,
                    'name' => 'Test Item'
                ]
            ]
        ];
        
        $result = $service->createTransaction($testData);
        
        return response()->json([
            'status' => $result['status'],
            'message' => $result['status'] === 'success' ? 'Koneksi berhasil!' : 'Gagal',
            'data' => $result,
            'server_key' => substr(config('midtrans.server_key'), 0, 10) . '...',
            'is_production' => config('midtrans.is_production'),
            'base_url' => $service->isProduction() ? 'Production' : 'Sandbox'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
require __DIR__ . '/auth.php';