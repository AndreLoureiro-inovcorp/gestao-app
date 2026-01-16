<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CalendarActionController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CalendarTypeController;
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactRoleController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierInvoiceController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\TenantController;
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

    // Dashboard (todos acedem)
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Tenant Switcher (todos acedem)
    Route::post('/tenants/switch/{tenant}', [TenantController::class, 'switch'])
        ->name('tenants.switch');

    // Users
    Route::middleware('permission:users')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Roles
    Route::middleware('permission:roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show', 'create']);
    });

    // Entities (Clientes/Fornecedores)
    Route::middleware('permission:entities')->group(function () {
        Route::resource('entities', EntityController::class);
    });

    // Contacts
    Route::middleware('permission:contacts')->group(function () {
        Route::resource('contacts', ContactController::class);
    });

    // Proposals
    Route::middleware('permission:proposals')->group(function () {
        Route::resource('proposals', ProposalController::class);
        Route::get('proposals/{proposal}/pdf', [ProposalController::class, 'downloadPdf'])
            ->name('proposals.download-pdf');
        Route::post('proposals/{proposal}/convert-to-order', 
            [ClientOrderController::class, 'createFromProposal'])
            ->name('proposals.convert-to-order');
    });

    // Orders
    Route::middleware('permission:orders')->group(function () {
        Route::resource('client-orders', ClientOrderController::class);
        Route::get('client-orders/{clientOrder}/pdf', [ClientOrderController::class, 'downloadPdf'])
            ->name('client-orders.download-pdf');
        Route::post('client-orders/{clientOrder}/create-supplier-orders', 
            [ClientOrderController::class, 'createSupplierOrders'])
            ->name('client-orders.create-supplier-orders');
        Route::resource('supplier-orders', SupplierOrderController::class)
            ->only(['index', 'show', 'update', 'destroy']);
    });

    // Invoices
    Route::middleware('permission:invoices')->group(function () {
        Route::resource('supplier-invoices', SupplierInvoiceController::class)
            ->except(['edit', 'update']);
        Route::post('/supplier-invoices/{supplierInvoice}/send-payment-notification', 
            [SupplierInvoiceController::class, 'sendPaymentNotification'])
            ->name('supplier-invoices.send-payment-notification');
    });

    // Calendar
    Route::middleware('permission:calendar')->group(function () {
        Route::resource('calendar-events', CalendarEventController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

    // Settings (Super Admin apenas ou permission:settings)
    Route::middleware('permission:settings')->group(function () {
        Route::resource('contact-roles', ContactRoleController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::resource('vat-rates', VatRateController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::resource('articles', ArticleController::class);
        Route::resource('countries', CountryController::class);
        Route::resource('calendar-types', CalendarTypeController::class)
            ->only(['index', 'store', 'destroy']);
        Route::resource('calendar-actions', CalendarActionController::class)
            ->only(['index', 'store', 'destroy']);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');
        Route::get('/settings/company', [CompanySettingController::class, 'index'])
            ->name('settings.company');
        Route::post('/settings/company', [CompanySettingController::class, 'update'])
            ->name('settings.company.update');
    });

});

require __DIR__.'/settings.php';
