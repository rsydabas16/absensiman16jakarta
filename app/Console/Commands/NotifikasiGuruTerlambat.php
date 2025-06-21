<?php
// ============================================================================
// 2. COMMAND: NOTIFIKASI GURU TERLAMBAT
// ============================================================================
// app/Console/Commands/NotifikasiGuruTerlambat.php

namespace App\Console\Commands;

use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifikasiGuruTerlambat extends Command
{
    protected $signature = 'notifikasi:guru-terlambat';
    protected $description = 'Kirim notifikasi ketika guru terlambat absen';
    
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }
    
    public function handle()
    {
        $now = Carbon::now();
        $hari = $now->locale('id')->dayName;
        $tanggal = $now->toDateString();
        
        // Ambil setting dari config
        $lateMinutes = config('telegram.notifications.late_minutes', 10);
        $waktuTerlambat = $now->copy()->subMinutes($lateMinutes)->format('H:i:00');
        
        // Cari jadwal yang dimulai X menit lalu dan belum diabsen
        $jadwalTerlambat = JadwalPelajaran::with(['guru.user', 'kelas', 'mataPelajaran'])
            ->where('hari', $hari)
            ->where(function($query) use ($waktuTerlambat, $now, $lateMinutes) {
                // Toleransi ±2 menit untuk akurasi
                $waktuMin = $now->copy()->subMinutes($lateMinutes + 2)->format('H:i:00');
                $waktuMax = $now->copy()->subMinutes($lateMinutes - 2)->format('H:i:00');
                $query->whereBetween('jam_mulai', [$waktuMin, $waktuMax]);
            })
            ->whereDoesntHave('absensiGuru', function ($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            })
            ->get();
            
        $this->info("Mencari jadwal terlambat untuk hari {$hari} yang dimulai sekitar {$lateMinutes} menit lalu");
        $this->info("Ditemukan {$jadwalTerlambat->count()} jadwal terlambat");
            
        foreach ($jadwalTerlambat as $jadwal) {
            // Kirim notifikasi ke guru
            if ($jadwal->guru && $jadwal->guru->user && $jadwal->guru->user->telegram_chat_id) {
                $result = $this->telegramService->sendTerlambatNotificationToGuru($jadwal->guru, $jadwal);
                if ($result) {
                    $this->info("Notifikasi terlambat terkirim ke guru: {$jadwal->guru->nama_lengkap}");
                } else {
                    $this->error("Gagal mengirim notifikasi terlambat ke guru: {$jadwal->guru->nama_lengkap}");
                }
            }
            
            // Kirim notifikasi ke siswa (ketua kelas)
            $resultSiswa = $this->telegramService->sendTerlambatNotificationToStudents($jadwal);
            if ($resultSiswa) {
                $this->info("Notifikasi terlambat terkirim ke siswa kelas: {$jadwal->kelas->nama_kelas}");
            } else {
                $this->warn("Gagal mengirim notifikasi terlambat ke siswa kelas: {$jadwal->kelas->nama_kelas}");
            }
        }
        
        $this->info("Total jadwal terlambat diproses: {$jadwalTerlambat->count()}");
    }
}





// namespace App\Console\Commands;

// use App\Models\JadwalPelajaran;
// use App\Models\AbsensiGuru;
// use App\Services\TelegramService;
// use Carbon\Carbon;
// use Illuminate\Console\Command;

// class NotifikasiGuruTerlambat extends Command
// {
//     protected $signature = 'notifikasi:guru-terlambat';
//     protected $description = 'Kirim notifikasi ketika guru terlambat absen';
    
//     protected $telegramService;
    
//     public function __construct(TelegramService $telegramService)
//     {
//         parent::__construct();
//         $this->telegramService = $telegramService;
//     }
    
//     public function handle()
//     {
//         $now = Carbon::now();
//         $hari = $now->locale('id')->dayName;
//         $tanggal = $now->toDateString();
//         $waktu10MenitLalu = $now->copy()->subMinutes(10)->format('H:i:00');
        
//         // Cari jadwal yang dimulai 10 menit lalu dan belum diabsen
//         $jadwalTerlambat = JadwalPelajaran::with(['guru.user', 'kelas', 'mataPelajaran'])
//             ->where('hari', $hari)
//             ->where('jam_mulai', $waktu10MenitLalu)
//             ->whereDoesntHave('absensiGuru', function ($query) use ($tanggal) {
//                 $query->whereDate('tanggal', $tanggal);
//             })
//             ->get();
            
//         $this->info("Mencari jadwal terlambat untuk hari {$hari} yang dimulai jam {$waktu10MenitLalu}");
//         $this->info("Ditemukan {$jadwalTerlambat->count()} jadwal terlambat");
            
//         foreach ($jadwalTerlambat as $jadwal) {
//             // Kirim notifikasi ke guru
//             if ($jadwal->guru && $jadwal->guru->user && $jadwal->guru->user->telegram_chat_id) {
//                 $result = $this->telegramService->sendTerlambatNotificationToGuru($jadwal->guru, $jadwal);
//                 if ($result) {
//                     $this->info("Notifikasi terlambat terkirim ke guru: {$jadwal->guru->nama_lengkap}");
//                 }
//             }
            
//             // Kirim notifikasi ke siswa (ketua kelas)
//             $resultSiswa = $this->telegramService->sendTerlambatNotificationToStudents($jadwal);
//             if ($resultSiswa) {
//                 $this->info("Notifikasi terlambat terkirim ke siswa kelas: {$jadwal->kelas->nama_kelas}");
//             }
//         }
        
//         $this->info("Total notifikasi terlambat terkirim: {$jadwalTerlambat->count()}");
//     }
// }
