<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->middleware('can:dashboard.view')->name('dashboard');

    Route::get('partners', [PartnerController::class, 'index'])->middleware('can:partners.view')->name('partners.index');
    Route::get('partners/create', [PartnerController::class, 'create'])->middleware('can:partners.create')->name('partners.create');
    Route::post('partners', [PartnerController::class, 'store'])->middleware('can:partners.create')->name('partners.store');
    Route::get('partners/{partner}', [PartnerController::class, 'show'])->middleware('can:partners.view')->name('partners.show');
    Route::get('partners/{partner}/edit', [PartnerController::class, 'edit'])->middleware('can:partners.update')->name('partners.edit');
    Route::put('partners/{partner}', [PartnerController::class, 'update'])->middleware('can:partners.update')->name('partners.update');

    Route::get('agreements', [AgreementController::class, 'index'])->middleware('can:agreements.view')->name('agreements.index');
    Route::get('agreements/create', [AgreementController::class, 'create'])->middleware('can:agreements.create')->name('agreements.create');
    Route::post('agreements', [AgreementController::class, 'store'])->middleware('can:agreements.create')->name('agreements.store');
    Route::get('agreements/{agreement}', [AgreementController::class, 'show'])->middleware('can:agreements.view')->name('agreements.show');
    Route::get('agreements/{agreement}/edit', [AgreementController::class, 'edit'])->middleware('can:agreements.update')->name('agreements.edit');
    Route::put('agreements/{agreement}', [AgreementController::class, 'update'])->middleware('can:agreements.update')->name('agreements.update');
});

require __DIR__.'/settings.php';
