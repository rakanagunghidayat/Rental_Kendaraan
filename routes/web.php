<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\PerawatanKendaraanController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\WebsiteSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/kendaraan/{kendaraan}', [KendaraanController::class, 'show'])->name('kendaraan.show');

// Halaman Landing Page (Utama)
Route::get('/', [KendaraanController::class, 'index']);
Route::get('/checkout/{kendaraan}', [KendaraanController::class, 'checkout'])->name('checkout.show');
Route::post('/checkout/{kendaraan}', [TransaksiController::class, 'store'])->name('checkout.store');

// Route untuk Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Halaman Dashboard Admin (Hanya bisa diakses jika sudah login & ber-role admin)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [KendaraanController::class, 'dashboard'])->name('dashboard');
    Route::post('/tambah-kendaraan', [KendaraanController::class, 'store'])->name('kendaraan.store');
    Route::get('/edit-kendaraan/{kendaraan}', [KendaraanController::class, 'edit'])->name('kendaraan.edit');
    Route::put('/update-kendaraan/{kendaraan}', [KendaraanController::class, 'update'])->name('kendaraan.update');
    Route::delete('/kendaraan/{kendaraan}', [KendaraanController::class, 'destroy'])->name('kendaraan.destroy');
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::patch('/transaksi/{transaksi}/status', [TransaksiController::class, 'updateStatus'])->name('transaksi.status');
    Route::post('/notifications/{notificationId}/read', [TransaksiController::class, 'markNotificationRead'])
        ->name('notifications.read');
    Route::get('/pelanggan', [TransaksiController::class, 'pelanggan'])->name('pelanggan.index');
    Route::get('/pelanggan/{pelanggan}/ktp', [TransaksiController::class, 'downloadKtp'])->name('pelanggan.ktp');

    Route::middleware('admin')->group(function () {
        Route::get('/perawatan-kendaraan', [PerawatanKendaraanController::class, 'index'])
            ->name('perawatan.index');
        Route::post('/perawatan-kendaraan', [PerawatanKendaraanController::class, 'store'])
            ->name('perawatan.store');
        Route::get('/perawatan-kendaraan/{perawatan}/edit', [PerawatanKendaraanController::class, 'edit'])
            ->name('perawatan.edit');
        Route::put('/perawatan-kendaraan/{perawatan}', [PerawatanKendaraanController::class, 'update'])
            ->name('perawatan.update');
        Route::delete('/perawatan-kendaraan/{perawatan}', [PerawatanKendaraanController::class, 'destroy'])
            ->name('perawatan.destroy');
        Route::get('/perawatan-kendaraan/kendaraan/{kendaraan}/riwayat', [PerawatanKendaraanController::class, 'history'])
            ->name('perawatan.history');

        Route::get('/pengaturan-website', [WebsiteSettingsController::class, 'edit'])
            ->name('pengaturan-website.edit');
        Route::put('/pengaturan-website', [WebsiteSettingsController::class, 'update'])
            ->name('pengaturan-website.update');
    });
});
