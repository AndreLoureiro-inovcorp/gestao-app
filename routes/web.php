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

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // Tenant Management
    Route::prefix('tenants')->group(function () {
        Route::get('create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/', [TenantController::class, 'store'])->name('tenants.store');
        Route::post('switch/{tenant}', [TenantController::class, 'switch'])->name('tenants.switch');
    });

    // Users (com limite de plano)
    Route::middleware('permission:users')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->middleware('check.limit:users')->name('users.store');
        Route::put('{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Plans
    Route::get('plans', [TenantController::class, 'plans'])->name('plans.index');
    Route::post('plans/{plan}', [TenantController::class, 'changePlan'])->name('plans.change');

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

    // Proposals (com limite de plano)
    Route::middleware('permission:proposals')->prefix('proposals')->group(function () {
        Route::get('/', [ProposalController::class, 'index'])->name('proposals.index');
        Route::get('create', [ProposalController::class, 'create'])->name('proposals.create');
        Route::post('/', [ProposalController::class, 'store'])->middleware('check.limit:proposals')->name('proposals.store');
        Route::get('{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
        Route::get('{proposal}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
        Route::put('{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
        Route::delete('{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');
        Route::get('{proposal}/pdf', [ProposalController::class, 'downloadPdf'])->name('proposals.download-pdf');
        Route::post('{proposal}/convert-to-order', [ClientOrderController::class, 'createFromProposal'])->name('proposals.convert-to-order');
    });

    // Orders
    Route::middleware('permission:orders')->group(function () {
        Route::resource('client-orders', ClientOrderController::class);
        Route::get('client-orders/{clientOrder}/pdf', [ClientOrderController::class, 'downloadPdf'])->name('client-orders.download-pdf');
        Route::post('client-orders/{clientOrder}/create-supplier-orders', [ClientOrderController::class, 'createSupplierOrders'])->name('client-orders.create-supplier-orders');
        Route::resource('supplier-orders', SupplierOrderController::class)->only(['index', 'show', 'update', 'destroy']);
    });

    // Invoices
    Route::middleware('permission:invoices')->group(function () {
        Route::resource('supplier-invoices', SupplierInvoiceController::class)->except(['edit', 'update']);
        Route::post('supplier-invoices/{supplierInvoice}/send-payment-notification', [SupplierInvoiceController::class, 'sendPaymentNotification'])->name('supplier-invoices.send-payment-notification');
    });

    // Calendar
    Route::middleware('permission:calendar')->group(function () {
        Route::resource('calendar-events', CalendarEventController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Settings
    Route::middleware('permission:settings')->group(function () {
        Route::resource('contact-roles', ContactRoleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('vat-rates', VatRateController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('articles', ArticleController::class);
        Route::resource('countries', CountryController::class);
        Route::resource('calendar-types', CalendarTypeController::class)->only(['index', 'store', 'destroy']);
        Route::resource('calendar-actions', CalendarActionController::class)->only(['index', 'store', 'destroy']);
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('settings/company', [CompanySettingController::class, 'index'])->name('settings.company');
        Route::post('settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');
    });

});

require __DIR__.'/settings.php';
