<?php

use App\Http\Controllers\BorrowController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/offline', function () {
    return view('vendor.laravelpwa.offline');
});
    

Route::get('/test/qr/{id?}', [InventarisController::class, 'show'])->name('test_qr');

Route::middleware('auth')->group(function () {
    Route::get('/', [InventarisController::class, 'index'])->name('home')->middleware('permission:VIEW_INVENTARIS');

    Route::get('/scan', [QrCodeController::class, 'showScanPage'])->name('qr.scan');
    Route::post('/process-scan', [QrCodeController::class, 'processScan'])->name('qr.process');

    Route::group(['prefix' => '/inventaris', 'as' => 'inventaris.'], function () {
        Route::get('/form/{id?}', [InventarisController::class, 'form'])->name('form');
        Route::post('/store', [InventarisController::class, 'store'])->name('store');
        Route::put('/update/{id}', [InventarisController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [InventarisController::class, 'destroy'])->name('delete');
    });

    Route::group(['prefix' => '/profile', 'as' => 'profile.'], function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => '/borrow', 'as' => 'borrow.'], function (){
         Route::get('/', [BorrowController::class, 'index'])->name('index'); // Menampilkan daftar peminjaman
    Route::get('/form/{id?}', [BorrowController::class, 'form'])->name('form'); // Form tambah/edit peminjaman
    Route::post('/store', [BorrowController::class, 'store'])->name('store'); // Simpan data baru
    Route::put('/update/{id}', [BorrowController::class, 'update'])->name('update'); // Update data peminjaman
    Route::delete('/delete/{id}', [BorrowController::class, 'destroy'])->name('delete'); // Hapus data peminjaman
    });

    Route::group(['prefix' => '/users', 'as' => 'users.'], function (){
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/form/{id?}', [UserController::class, 'form'])->name('form');
        Route::put('/{id}/update-role', [UserController::class, 'update'])->name('update');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => '/roles', 'as' => 'roles.'], function (){
        Route::get('/', [RolePermissionController::class, 'manageRoles'])->name('index');
        Route::get('/form/{id?}', [RolePermissionController::class, 'form'])->name('form');
        Route::put('/{role}/update-permissions', [RolePermissionController::class, 'updateRolePermissions'])->name('update');
        Route::post('/store', [RolePermissionController::class, 'store'])->name('store');
        Route::delete('/delete/{role}', [RolePermissionController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
