<?php

use App\Http\Controllers\ExportController;
use App\Livewire\Dashboard;
use App\Livewire\Login;
use App\Livewire\PosCashier;
use App\Livewire\ProductManager;
use App\Livewire\SalesReport;
use App\Livewire\TransactionHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('pos')
        : redirect()->route('login');
});

// ─── Login (halaman publik) ─────────────────────────────────
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

// ─── Semua halaman butuh login (keamanan toko) ──────────────
Route::middleware('auth')->group(function () {
    Route::get('/pos', PosCashier::class)->name('pos');
    Route::get('/produk', ProductManager::class)->name('produk');
    Route::get('/transaksi', TransactionHistory::class)->name('transaksi');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/laporan', SalesReport::class)->name('laporan');

    // ─── Export ──────────────────────────────────────────────
    Route::get('/export/transaksi/excel', [ExportController::class, 'exportExcel'])->name('export.transaksi.excel');
    Route::get('/export/transaksi/pdf', [ExportController::class, 'exportPdf'])->name('export.transaksi.pdf');
    Route::get('/export/laporan/excel', [ExportController::class, 'exportSalesReportExcel'])->name('export.laporan.excel');
    Route::get('/export/laporan/pdf', [ExportController::class, 'exportSalesReportPdf'])->name('export.laporan.pdf');
});

