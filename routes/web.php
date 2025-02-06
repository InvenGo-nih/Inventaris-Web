<?php

use App\Http\Controllers\BorrowController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/offline', function () {
    return view('vendor.laravelpwa.offline');
});
    

Route::get('/test/qr/{id?}', [InventarisController::class, 'show'])->name('test_qr');

Route::middleware('auth')->group(function () {
    Route::get('/', [InventarisController::class, 'index'])->name('home');

    Route::get('/scan', [QrCodeController::class, 'showScanPage'])->name('qr.scan');
    Route::post('/process-scan', [QrCodeController::class, 'processScan'])->name('qr.process');

    Route::group(['prefix' => '/inventaris', 'as' => 'inventaris.'], function () {
        Route::get('/form/{id?}', [InventarisController::class, 'form'])->name('form');
        Route::post('/store', [InventarisController::class, 'store'])->name('store');
        Route::put('/update/{id}', [InventarisController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [InventarisController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => '/profile', 'as' => 'profile.'], function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => '/borrow', 'as' => 'borrow.'], function (){
        Route::get('/', [BorrowController::class, 'index'])->name('index');
        Route::get('/form/{id?}', [BorrowController::class, 'form'])->name('form');
    });
});

require __DIR__.'/auth.php';
