<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ObligationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware(['auth','verified'])->group(function () {
    Route::view('dashboard','dashboard')->middleware('can:dashboard.view')->name('dashboard');
    Route::get('reports',[ReportController::class,'index'])->middleware('can:reports.view')->name('reports.index');
    Route::get('audit',[AuditController::class,'index'])->middleware('can:audit.view')->name('audit.index');
    Route::get('partners',[PartnerController::class,'index'])->middleware('can:partners.view')->name('partners.index');
    Route::get('partners/create',[PartnerController::class,'create'])->middleware('can:partners.create')->name('partners.create');
    Route::post('partners',[PartnerController::class,'store'])->middleware('can:partners.create')->name('partners.store');
    Route::get('partners/{partner}',[PartnerController::class,'show'])->middleware('can:partners.view')->name('partners.show');
    Route::get('partners/{partner}/edit',[PartnerController::class,'edit'])->middleware('can:partners.update')->name('partners.edit');
    Route::put('partners/{partner}',[PartnerController::class,'update'])->middleware('can:partners.update')->name('partners.update');
    Route::get('agreements',[AgreementController::class,'index'])->middleware('can:agreements.view')->name('agreements.index');
    Route::get('agreements/create',[AgreementController::class,'create'])->middleware('can:agreements.create')->name('agreements.create');
    Route::post('agreements',[AgreementController::class,'store'])->middleware('can:agreements.create')->name('agreements.store');
    Route::get('agreements/{agreement}',[AgreementController::class,'show'])->middleware('can:agreements.view')->name('agreements.show');
    Route::get('agreements/{agreement}/edit',[AgreementController::class,'edit'])->middleware('can:agreements.update')->name('agreements.edit');
    Route::put('agreements/{agreement}',[AgreementController::class,'update'])->middleware('can:agreements.update')->name('agreements.update');
    Route::get('approvals',[ApprovalController::class,'index'])->middleware('can:approvals.view')->name('approvals.index');
    Route::post('agreements/{agreement}/submit',[ApprovalController::class,'submit'])->middleware('can:agreements.update')->name('agreements.submit');
    Route::post('agreements/{agreement}/review',[ApprovalController::class,'review'])->middleware('can:approvals.review')->name('agreements.review');
    Route::get('obligations',[ObligationController::class,'index'])->middleware('can:obligations.view')->name('obligations.index');
    Route::get('agreements/{agreement}/obligations/create',[ObligationController::class,'create'])->middleware('can:obligations.manage')->name('obligations.create');
    Route::post('agreements/{agreement}/obligations',[ObligationController::class,'store'])->middleware('can:obligations.manage')->name('obligations.store');
    Route::get('obligations/{obligation}/edit',[ObligationController::class,'edit'])->middleware('can:obligations.manage')->name('obligations.edit');
    Route::put('obligations/{obligation}',[ObligationController::class,'update'])->middleware('can:obligations.manage')->name('obligations.update');
    Route::patch('obligations/{obligation}/progress',[ObligationController::class,'progress'])->middleware('can:obligations.manage')->name('obligations.progress');
    Route::delete('obligations/{obligation}',[ObligationController::class,'destroy'])->middleware('can:obligations.manage')->name('obligations.destroy');
    Route::get('documents',[DocumentController::class,'index'])->middleware('can:documents.view')->name('documents.index');
    Route::get('agreements/{agreement}/documents/create',[DocumentController::class,'create'])->middleware('can:documents.manage')->name('documents.create');
    Route::post('agreements/{agreement}/documents',[DocumentController::class,'store'])->middleware('can:documents.manage')->name('documents.store');
    Route::get('documents/{document}/download',[DocumentController::class,'download'])->middleware('can:documents.view')->name('documents.download');
    Route::delete('documents/{document}',[DocumentController::class,'destroy'])->middleware('can:documents.manage')->name('documents.destroy');
});
require __DIR__.'/settings.php';