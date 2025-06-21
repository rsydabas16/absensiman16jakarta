<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\JadwalPelajaranController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Guru\GuruDashboardController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Siswa\SiswaDashboardController;
use App\Http\Controllers\Siswa\GenerateQrController;
use App\Http\Controllers\Siswa\MateriController;
use App\Http\Controllers\KepalaSekolah\KepalaSekolahDashboardController;
use App\Http\Controllers\KepalaSekolah\LaporanController;
use App\Http\Controllers\KepalaSekolah\StatistikController;
use App\Http\Controllers\TelegramController;

// Auth Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Profile Routes (Authenticated users) - Tambahkan setelah route logout
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// routes/web.php
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Tambahkan route reset password
    Route::post('/guru/{id}/reset-password', [App\Http\Controllers\Admin\GuruController::class, 'resetPassword'])->name('guru.reset-password');

    Route::post('/siswa/{id}/reset-password', [App\Http\Controllers\Admin\SiswaController::class, 'resetPassword'])->name('siswa.reset-password');

    
    // User Management
    Route::resource('users', UserController::class);
    Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
    
    // Guru Management (jika ada controller terpisah)
    Route::resource('guru', GuruController::class);
    Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
    
    // Siswa Management (jika ada controller terpisah)
    Route::resource('siswa', SiswaController::class);
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    
    // Kelas Management
    Route::resource('kelas', KelasController::class);
    Route::post('kelas/import', [KelasController::class, 'import'])->name('kelas.import');
    
    // Mata Pelajaran Management
    Route::resource('mata-pelajaran', MataPelajaranController::class);
    Route::post('mata-pelajaran/import', [MataPelajaranController::class, 'import'])->name('mata-pelajaran.import');
    
    // Jadwal Pelajaran Management
    Route::resource('jadwal-pelajaran', JadwalPelajaranController::class);
    Route::post('jadwal-pelajaran/import', [JadwalPelajaranController::class, 'import'])->name('jadwal-pelajaran.import');
    
    // Hari Libur Management
    Route::resource('hari-libur', HariLiburController::class);
     Route::resource('hari-libur', HariLiburController::class);
    Route::post('hari-libur/import', [HariLiburController::class, 'import'])->name('hari-libur.import');
    
    // Laporan Admin
    Route::get('laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export', [AdminLaporanController::class, 'export'])->name('laporan.export');

     // Absensi Siswa routes
    Route::prefix('absensi-siswa')->name('absensi-siswa.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AbsensiSiswaController::class, 'index'])->name('index');
        Route::get('/export-excel', [App\Http\Controllers\Admin\AbsensiSiswaController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [App\Http\Controllers\Admin\AbsensiSiswaController::class, 'exportPdf'])->name('export-pdf');
    });
});

// Guru Routes
Route::middleware(['auth', 'guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

    
    
    // Absensi routes
    Route::middleware(['cek.hari.libur'])->group(function () {
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/create', [AbsensiController::class, 'create'])->name('absensi.create');
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::post('/absensi/scan-qr', [AbsensiController::class, 'scanQr'])->name('absensi.scan-qr');
    
    });
    
    // Rekap routes
    Route::get('/rekap', [AbsensiController::class, 'rekap'])->name('rekap.index');
    Route::get('/rekap/export', [AbsensiController::class, 'exportRekap'])->name('rekap.export');
     Route::get('/rekap/export-pdf', [AbsensiController::class, 'exportRekapPdf'])->name('rekap.export-pdf');
       // Tambahkan route baru ini:
    Route::get('/rekap/weeks-in-month', [AbsensiController::class, 'getWeeksInMonth'])
        ->name('rekap.weeks-in-month');

});

// Siswa Routes (Ketua/Wakil)
Route::middleware(['auth', 'siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    
    // Generate QR routes
    Route::prefix('generate-qr')->name('generate-qr.')->group(function () {
        Route::get('/', [GenerateQrController::class, 'index'])->name('index');
        Route::get('/create', [GenerateQrController::class, 'create'])->name('create');
        Route::post('/regenerate', [GenerateQrController::class, 'regenerate'])->name('regenerate');
    });
    
    // Materi routes
    Route::prefix('materi')->name('materi.')->group(function () {
        Route::get('/', [MateriController::class, 'index'])->name('index');
        Route::get('/create', [MateriController::class, 'create'])->name('create');
        Route::post('/', [MateriController::class, 'store'])->name('store');
        Route::get('/tugas-guru', [MateriController::class, 'tugasGuru'])->name('tugas-guru');
    });

     // Absensi Siswa routes
    Route::prefix('absensi-siswa')->name('absensi-siswa.')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\AbsensiSiswaController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Siswa\AbsensiSiswaController::class, 'store'])->name('store');
        Route::get('/rekap', [App\Http\Controllers\Siswa\AbsensiSiswaController::class, 'rekap'])->name('rekap');
    });
});



// Route Kepala Sekolah
Route::middleware(['auth', 'kepala.sekolah'])->prefix('kepala_sekolah')->name('kepala_sekolah.')->group(function () {
    Route::get('/dashboard', [KepalaSekolahDashboardController::class, 'index'])->name('dashboard');
    
    // Route laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/export', [LaporanController::class, 'export'])->name('export');
    });
    
    // Route statistik
    Route::prefix('statistik')->name('statistik.')->group(function () {
        Route::get('/', [StatistikController::class, 'index'])->name('index');
        Route::get('/{guru}', [StatistikController::class, 'detail'])->name('detail');
    });

     // Absensi Siswa routes
    Route::prefix('absensi-siswa')->name('absensi-siswa.')->group(function () {
        Route::get('/', [App\Http\Controllers\KepalaSekolah\AbsensiSiswaController::class, 'index'])->name('index');
        Route::get('/export-excel', [App\Http\Controllers\KepalaSekolah\AbsensiSiswaController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [App\Http\Controllers\KepalaSekolah\AbsensiSiswaController::class, 'exportPdf'])->name('export-pdf');
    });
    
});

// Telegram webhook
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);




// // Kepala Sekolah Routes
// Route::middleware(['auth', 'kepala.sekolah'])->prefix('kepala-sekolah')->name('kepala-sekolah.')->group(function () {
//     Route::get('/dashboard', [KepalaSekolahDashboardController::class, 'index'])->name('dashboard');
    
//     // Laporan routes
//     Route::prefix('laporan')->name('laporan.')->group(function () {
//         Route::get('/', [LaporanController::class, 'index'])->name('index');
//         Route::get('/export', [LaporanController::class, 'export'])->name('export');
//     });
    
//     // Statistik routes
//     Route::prefix('statistik')->name('statistik.')->group(function () {
//         Route::get('/', [StatistikController::class, 'index'])->name('index');
//         Route::get('/{guru}', [StatistikController::class, 'detail'])->name('detail');
//     });
// });