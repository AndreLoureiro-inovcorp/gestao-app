<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactRoleController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatRateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('entities', EntityController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('articles', ArticleController::class);
    Route::resource('countries', CountryController::class);
    Route::resource('users', UserController::class);
    Route::resource('proposals', ProposalController::class);
    Route::resource('client-orders', ClientOrderController::class);
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::resource('contact-roles', ContactRoleController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('vat-rates', VatRateController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('proposals/{proposal}/convert-to-order', [ClientOrderController::class, 'createFromProposal'])->name('proposals.convert-to-order');

    Route::post('client-orders/{clientOrder}/create-supplier-orders', [ClientOrderController::class, 'createSupplierOrders'])->name('client-orders.create-supplier-orders');

    Route::resource('supplier-orders', SupplierOrderController::class)->only(['index', 'show', 'update', 'destroy']);

    Route::get('/settings/company', [CompanySettingController::class, 'index'])->name('settings.company');
    Route::post('/settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');
});

require __DIR__.'/settings.php';
