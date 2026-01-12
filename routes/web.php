<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ProposalController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('entities', EntityController::class);
Route::resource('contacts', ContactController::class);
Route::resource('articles', ArticleController::class);
Route::resource('countries', CountryController::class);
Route::resource('users', UserController::class);
Route::resource('proposals', ProposalController::class);

Route::get('/settings/company', [CompanySettingController::class, 'index'])->name('settings.company');
    Route::post('/settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');

require __DIR__.'/settings.php';
