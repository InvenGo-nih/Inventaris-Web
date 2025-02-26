<?php

use App\Http\Controllers\BorrowController;
use App\Http\Controllers\DasboardController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\InventarisLocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::get('/offline', function () {
    return view('vendor.laravelpwa.offline');
});    

Route::get('/qr/{id?}', [InventarisController::class, 'showQr'])->name('test_qr');

Route::middleware('auth')->group(function () {
    Route::get('/', [DasboardController::class, 'index'])->middleware('permission:VIEW_DASHBOARD')->name('dashboard');
    Route::get('/chart_data', [DasboardController::class, 'chartData'])->middleware('permission:CHART_DATA')->name('chart_data');
    
    Route::get('/scan', [QrCodeController::class, 'showScanPage'])->name('qr.scan')->middleware('permission:VIEW_SCAN');
    Route::post('/process-scan', [QrCodeController::class, 'processScan'])->name('qr.process')->middleware('permission:SCAN_PROCESS');
    
    Route::group(['prefix' => '/inventaris', 'as' => 'inventaris.'], function () {
        Route::get('/', [InventarisController::class, 'index'])->name('index')->middleware('permission:VIEW_INVENTARIS');
        Route::get('/show/{id}', [InventarisController::class, 'show'])->name('show')->middleware('permission:SHOW_INVENTARIS');
        Route::get('/form/{id?}', [InventarisController::class, 'form'])->name('form')->middleware('permission:CREATE_INVENTARIS', 'permission:EDIT_INVENTARIS');
        Route::post('/store', [InventarisController::class, 'store'])->name('store')->middleware('permission:CREATE_INVENTARIS');
        Route::get('/pdf', [InventarisController::class, 'downloadPDF'])->name('pdf')->middleware('permission:PDF_INVENTARIS');
        Route::put('/update/{id}', [InventarisController::class, 'update'])->name('update')->middleware('permission:EDIT_INVENTARIS');
        Route::delete('/delete/{id}', [InventarisController::class, 'destroy'])->name('delete')->middleware('permission:DELETE_INVENTARIS');
    });

    Route::group(['prefix' => '/inventaris-location', 'as' => 'inventaris.location.'], function () {
        Route::get('/', [InventarisLocationController::class, 'index'])->name('index')->middleware('permission:VIEW_LOCATION_INVENTARIS');
        Route::post('/store', [InventarisLocationController::class, 'store'])->name('store')->middleware('permission:CREATE_LOCATION_INVENTARIS');
        Route::put('/update/{id}', [InventarisLocationController::class, 'update'])->name('update')->middleware('permission:EDIT_LOCATION_INVENTARIS');
        Route::delete('/delete/{id}', [InventarisLocationController::class, 'destroy'])->name('delete')->middleware('permission:DELETE_LOCATION_INVENTARIS');
    });

    // Route::group(['prefix' => '/profile', 'as' => 'profile.'], function () {
    //     Route::get('/', [ProfileController::class, 'edit'])->name('edit')->middleware('permission:VIEW_PROFILE');
    //     Route::patch('/', [ProfileController::class, 'update'])->name('update')->middleware('permission:EDIT_PROFILE');
    //     Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy')->middleware('permission:DELETE_PROFILE');
    // });

    Route::group(['prefix' => '/borrow', 'as' => 'borrow.'], function (){
         Route::get('/', [BorrowController::class, 'index'])->name('index')->middleware('permission:VIEW_BORROW'); // Menampilkan daftar peminjaman
    Route::get('/form/{id?}', [BorrowController::class, 'form'])->name('form')->middleware('permission:CREATE_BORROW', 'permission:EDIT_BORROW'); // Form tambah/edit peminjaman
    Route::post('/store', [BorrowController::class, 'store'])->name('store')->middleware('permission:CREATE_BORROW'); // Simpan data baru
    Route::put('/update/{id}', [BorrowController::class, 'update'])->name('update')->middleware('permission:EDIT_BORROW'); // Update data peminjaman
    Route::delete('/delete/{id}', [BorrowController::class, 'destroy'])->name('delete')->middleware('permission:DELETE_BORROW'); // Hapus data peminjaman
    });

    Route::group(['prefix' => '/users', 'as' => 'users.'], function (){
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:VIEW_USERS');
        Route::get('/form/{id?}', [UserController::class, 'form'])->name('form')->middleware('permission:CREATE_USERS', 'permission:EDIT_USERS');
        Route::put('/{id}/update-role', [UserController::class, 'update'])->name('update')->middleware('permission:EDIT_USERS');
        Route::post('/store', [UserController::class, 'store'])->name('store')->middleware('permission:CREATE_USERS');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('destroy')->middleware('permission:DELETE_USERS');
    });

    Route::group(['prefix' => '/roles', 'as' => 'roles.'], function (){
        Route::get('/', [RolePermissionController::class, 'manageRoles'])->name('index')->middleware('permission:VIEW_ROLES');
        Route::get('/form/{id?}', [RolePermissionController::class, 'form'])->name('form')->middleware('permission:CREATE_ROLES', 'permission:EDIT_ROLES');
        Route::put('/{role}/update-permissions', [RolePermissionController::class, 'updateRolePermissions'])->name('update')->middleware('permission:EDIT_ROLES');
        Route::post('/store', [RolePermissionController::class, 'store'])->name('store')->middleware('permission:CREATE_ROLES');
        Route::delete('/delete/{role}', [RolePermissionController::class, 'destroy'])->name('destroy')->middleware('permission:DELETE_ROLES');
    });
});

require __DIR__.'/auth.php';
