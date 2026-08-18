<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\DriverManagementController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\MitraDashboardController;
use App\Http\Controllers\MitraManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevenueShareController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::post('/payments/midtrans/notification', [PaymentController::class, 'notification'])
    ->name('payments.notification');

Route::get('/payments/statuses', [PaymentController::class, 'statuses'])
    ->middleware('auth')
    ->name('payments.statuses');

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->role === 'mitra') {
        return redirect()->route('mitra.dashboard');
    }

    if ($user?->role === 'driver') {
        return redirect()->route('driver.dashboard');
    }

    return redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/bookings', [BookingController::class, 'adminBoard'])->name('admin.bookings.index');
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::post('/vehicles/{vehicle}/approve', [VehicleController::class, 'approve'])->name('admin.vehicles.approve');
    Route::post('/vehicles/{vehicle}/assign-driver', [VehicleController::class, 'assignDriver'])->name('admin.vehicles.assign-driver');
    Route::get('/drivers', [DriverManagementController::class, 'index'])->name('admin.drivers.index');
    Route::post('/drivers', [DriverManagementController::class, 'store'])->name('admin.drivers.store');
    Route::get('/financial-reports', [FinancialReportController::class, 'index'])->name('admin.reports.index');
    Route::post('/bookings/{booking}/assign-driver', [BookingController::class, 'assignDriver'])->name('admin.bookings.assign-driver');
    Route::post('/bookings/{booking}/assign-vehicle', [BookingController::class, 'assignVehicle'])->name('admin.bookings.assign-vehicle');
    Route::post('/bookings/{booking}/generate-ticket', [BookingController::class, 'generateTicket'])->name('admin.bookings.generate-ticket');
    Route::post('/bookings/{booking}/refund', [BookingController::class, 'refund'])->name('admin.bookings.refund');
    Route::post('/bookings/{booking}/refund/approve', [BookingController::class, 'approveRefund'])->name('admin.bookings.refund.approve');
    Route::post('/bookings/{booking}/refund/reject', [BookingController::class, 'rejectRefund'])->name('admin.bookings.refund.reject');
    Route::post('/bookings/{booking}/mark-paid', [BookingController::class, 'markAsFullyPaid'])->name('admin.bookings.mark-paid');
    Route::get('/mitras', [MitraManagementController::class, 'index'])->name('admin.mitras.index');
    Route::post('/mitras', [MitraManagementController::class, 'store'])->name('admin.mitras.store');
    Route::get('/revenue-shares', [RevenueShareController::class, 'index'])->name('admin.revenue-shares.index');
    Route::post('/revenue-shares', [RevenueShareController::class, 'store'])->name('admin.revenue-shares.store');
    Route::get('/revenue-shares/{revenueShare}/edit', [RevenueShareController::class, 'edit'])->name('admin.revenue-shares.edit');
    Route::put('/revenue-shares/{revenueShare}', [RevenueShareController::class, 'update'])->name('admin.revenue-shares.update');
    Route::delete('/revenue-shares/{revenueShare}', [RevenueShareController::class, 'destroy'])->name('admin.revenue-shares.destroy');
    Route::post('/payouts/{payout}/pay', [FinancialReportController::class, 'markPaid'])->name('admin.payouts.pay');
    Route::resource('/routes', RouteController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('admin.routes');
});

Route::middleware('auth')->group(function () {
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'markCompleted'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/ticket/{booking}', [BookingController::class, 'showTicket'])->name('ticket.show');
});

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->group(function () {
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('mitra.dashboard');
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::post('/vehicles/reorder', [VehicleController::class, 'reorder'])->name('vehicles.reorder');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});

Route::middleware(['auth', 'role:driver'])->prefix('driver')->group(function () {
    Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('driver.dashboard');
    Route::post('/route-assignments/{routeAssignment}/vehicles/{vehicle}/depart', [DriverDashboardController::class, 'depart'])->name('driver.route-assignments.depart');
    Route::get('/bookings', [BookingController::class, 'index'])->name('driver.bookings.index');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/bookings/{booking}/payment/status', [PaymentController::class, 'status'])->name('payments.status');
    Route::post('/bookings/{booking}/refund-request', [BookingController::class, 'requestRefund'])->name('bookings.refund.request');
});

require __DIR__.'/auth.php';
