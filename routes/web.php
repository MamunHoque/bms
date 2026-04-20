<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UtilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Buildings
    Route::resource('buildings', BuildingController::class);

    // Units
    Route::resource('units', UnitController::class);

    // Tenants
    Route::resource('tenants', TenantController::class);

    // Leases
    Route::resource('leases', LeaseController::class);
    Route::post('leases/{lease}/end', [LeaseController::class, 'end'])->name('leases.end');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/generate-monthly', [InvoiceController::class, 'generateMonthly'])->name('invoices.generate');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // Payments
    Route::resource('payments', PaymentController::class);

    // Utilities
    Route::get('utilities', [UtilityController::class, 'index'])->name('utilities.index');
    Route::get('utilities/types', [UtilityController::class, 'types'])->name('utilities.types');
    Route::post('utilities/types', [UtilityController::class, 'storeType'])->name('utilities.types.store');
    Route::put('utilities/types/{utilityType}', [UtilityController::class, 'updateType'])->name('utilities.types.update');
    Route::delete('utilities/types/{utilityType}', [UtilityController::class, 'destroyType'])->name('utilities.types.destroy');

    Route::get('utilities/readings/create', [UtilityController::class, 'createReading'])->name('utilities.readings.create');
    Route::post('utilities/readings', [UtilityController::class, 'storeReading'])->name('utilities.readings.store');
    Route::delete('utilities/readings/{reading}', [UtilityController::class, 'destroyReading'])->name('utilities.readings.destroy');

    // Maintenance
    Route::resource('maintenance', MaintenanceController::class)->except(['show']);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/collection', [ReportController::class, 'collection'])->name('reports.collection');
    Route::get('reports/dues', [ReportController::class, 'dues'])->name('reports.dues');
    Route::get('reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('reports/utilities', [ReportController::class, 'utilities'])->name('reports.utilities');
});
