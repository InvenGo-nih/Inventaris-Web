<?php

use App\Http\Controllers\BorrowController;
use App\Http\Controllers\CekController;
use App\Http\Controllers\DasboardController;
use App\Http\Controllers\GroupInventarisController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\InventarisLocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/qr/{id}', [InventarisController::class, 'showQr'])
        ->name('test_qr')
        ->middleware('permission:SHOW_INVENTARIS');

    Route::get('/', [DasboardController::class, 'index'])
        ->middleware('permission:VIEW_DASHBOARD')
        ->name('dashboard');
    Route::get('/chart_data', [DasboardController::class, 'chartData'])
        ->middleware('permission:VIEW_DASHBOARD')
        ->name('chart_data');

    Route::get('/scan', [QrCodeController::class, 'showScanPage'])
        ->name('qr.scan')
        ->middleware('permission:VIEW_SCAN');
    Route::post('/process-scan', [QrCodeController::class, 'processScan'])
        ->name('qr.process')
        ->middleware(['permission:SCAN_PROCESS', 'throttle:10,1']);

    Route::group(['prefix' => '/inventaris', 'as' => 'inventaris.'], function () {
        // 📁 Group Inventaris (dikelola oleh GroupInventarisController)
        Route::get('/', [GroupInventarisController::class, 'index'])
            ->name('index')
            ->middleware('permission:VIEW_INVENTARIS');

        Route::get('/form/group/{id?}', [GroupInventarisController::class, 'form'])
            ->name('formGroup')
            ->middleware('permission:CREATE_INVENTARIS,EDIT_INVENTARIS');

        Route::post('/storegroup', [GroupInventarisController::class, 'store'])
            ->name('storeGroup')
            ->middleware(['throttle:10,1', 'permission:CREATE_INVENTARIS']);

        Route::put('/updategroup/{id}', [GroupInventarisController::class, 'update'])
            ->name('updateGroup')
            ->middleware(['throttle:10,1', 'permission:EDIT_INVENTARIS']);

        Route::delete('/deletegroup/{id}', [GroupInventarisController::class, 'destroy'])
            ->name('deleteGroup')
            ->middleware(['throttle:10,1', 'permission:DELETE_INVENTARIS']);

        // 📦 Inventaris (dikelola oleh InventarisController)
        Route::get('/group/{id}', [InventarisController::class, 'index']) // ← ubah dari /{id}
            ->name('inventaris')
            ->middleware('permission:VIEW_INVENTARIS');

        Route::get('/show/{id}', [InventarisController::class, 'show'])
            ->name('show')
            ->middleware('permission:SHOW_INVENTARIS');

        Route::post('/store', [InventarisController::class, 'store'])
            ->name('store')
            ->middleware(['throttle:10,1', 'permission:CREATE_INVENTARIS']);

        Route::put('/update/{id}', [InventarisController::class, 'update'])
            ->name('update')
            ->middleware(['throttle:10,1', 'permission:EDIT_INVENTARIS']);

        Route::delete('/delete/{id}', [InventarisController::class, 'destroy'])
            ->name('delete')
            ->middleware(['throttle:10,1', 'permission:DELETE_INVENTARIS']);

        Route::get('/form/{group_inventaris_id}/{id?}', [InventarisController::class, 'form'])
            ->name('form')
            ->middleware('permission:CREATE_INVENTARIS,EDIT_INVENTARIS');

        Route::get('/pdf/{group_inventaris_id}', [InventarisController::class, 'downloadPDF'])
            ->name('pdf')
            ->middleware('permission:PDF_INVENTARIS');
    });

    Route::group(['prefix' => '/inventaris-location', 'as' => 'inventaris.location.'], function () {
        Route::get('/', [InventarisLocationController::class, 'index'])
            ->name('index')
            ->middleware('permission:VIEW_LOCATION_INVENTARIS');
        Route::post('/store', [InventarisLocationController::class, 'store'])
            ->name('store')
            ->middleware(['throttle:10,1', 'permission:CREATE_LOCATION_INVENTARIS']);
        Route::put('/update/{id}', [InventarisLocationController::class, 'update'])
            ->name('update')
            ->middleware(['throttle:10,1', 'permission:EDIT_LOCATION_INVENTARIS']);
        Route::delete('/delete/{id}', [InventarisLocationController::class, 'destroy'])
            ->name('delete')
            ->middleware(['throttle:10,1', 'permission:DELETE_LOCATION_INVENTARIS']);
    });

    Route::group(['prefix' => '/borrow', 'as' => 'borrow.'], function () {
        Route::get('/', [BorrowController::class, 'index'])
            ->name('index')
            ->middleware('permission:VIEW_BORROW'); // Menampilkan daftar peminjaman
        Route::get('/form/{id?}', [BorrowController::class, 'form'])
            ->name('form')
            ->middleware('permission:CREATE_BORROW,EDIT_BORROW'); // Form tambah/edit peminjaman
        Route::post('/store', [BorrowController::class, 'store'])
            ->name('store')
            ->middleware(['throttle:10,1', 'permission:CREATE_BORROW']); // Simpan data baru
        Route::put('/update/{id}', [BorrowController::class, 'update'])
            ->name('update')
            ->middleware(['throttle:10,1', 'permission:EDIT_BORROW']); // Update data peminjaman
        Route::delete('/delete/{id}', [BorrowController::class, 'destroy'])
            ->name('delete')
            ->middleware(['throttle:10,1', 'permission:DELETE_BORROW']); // Hapus data peminjaman
    });

    Route::group(['prefix' => '/users', 'as' => 'users.'], function () {
        Route::get('/', [UserController::class, 'index'])
            ->name('index')
            ->middleware('permission:VIEW_USERS');
        Route::get('/form/{id?}', [UserController::class, 'form'])
            ->name('form')
            ->middleware('permission:CREATE_USERS,EDIT_USERS');
        Route::put('/{id}/update-role', [UserController::class, 'update'])
            ->name('update')
            ->middleware(['throttle:10,1', 'permission:EDIT_USERS']);
        Route::post('/store', [UserController::class, 'store'])
            ->name('store')
            ->middleware(['throttle:10,1', 'permission:CREATE_USERS']);
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])
            ->name('destroy')
            ->middleware(['throttle:10,1', 'permission:DELETE_USERS']);
    });

    Route::group(['prefix' => '/roles', 'as' => 'roles.'], function () {
        Route::get('/', [RolePermissionController::class, 'manageRoles'])
            ->name('index')
            ->middleware('permission:VIEW_ROLES');
        Route::get('/form/{id?}', [RolePermissionController::class, 'form'])
            ->name('form')
            ->middleware('permission:CREATE_ROLES,EDIT_ROLES');
        Route::put('/{role}/update-permissions', [RolePermissionController::class, 'updateRolePermissions'])
            ->name('update')
            ->middleware(['throttle:10,1', 'permission:EDIT_ROLES']);
        Route::post('/store', [RolePermissionController::class, 'store'])
            ->name('store')
            ->middleware(['throttle:10,1', 'permission:CREATE_ROLES']);
        Route::delete('/delete/{role}', [RolePermissionController::class, 'destroy'])
            ->name('destroy')
            ->middleware(['throttle:10,1', 'permission:DELETE_ROLES']);
    });

    Route::group(['prefix' => '/cek', 'as' => 'cek.'], function () {
        Route::get('/', [CekController::class, 'index'])->name('index')->middleware('permission:VIEW_CEK');
        Route::get('/{id}', [CekController::class, 'cek'])->name('cek')->middleware('permission:PROCESS_CEK');
        Route::put('/{id}', [CekController::class, 'update'])->name('update')->middleware(['throttle:10,1', 'permission:PROCESS_CEK']);
    });

    Route::get('/notifications/overdue', [NotificationController::class, 'overdueNotifications'])
        ->name('notifications.overdue')
        ->middleware('permission:VIEW_BORROW');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:10,1');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:10,1');
});

require __DIR__.'/auth.php';
