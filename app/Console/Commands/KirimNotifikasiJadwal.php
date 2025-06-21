<?php
// app/Console/Commands/KirimNotifikasiJadwal.php

namespace App\Console\Commands;

use App\Models\JadwalPelajaran;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class KirimNotifikasiJadwal extends Command
{
    protected $signature = 'notifikasi:jadwal';
    protected $description = 'Kirim notifikasi jadwal mengajar ke guru';
    
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
        $waktu15MenitLagi = $now->copy()->addMinutes(1)->format('H:i:00');
        
        // Cari jadwal yang akan dimulai 15 menit lagi
        $jadwalList = JadwalPelajaran::with(['guru.user', 'kelas', 'mataPelajaran'])
            ->where('hari', $hari)
            ->where('jam_mulai', $waktu15MenitLagi)
            ->get();
            
        $this->info("Mencari jadwal untuk hari {$hari} pada jam {$waktu15MenitLagi}");
        $this->info("Ditemukan {$jadwalList->count()} jadwal");
            
        foreach ($jadwalList as $jadwal) {
            if ($jadwal->guru && $jadwal->guru->user && $jadwal->guru->user->telegram_chat_id) {
                $result = $this->telegramService->sendNotificationToGuru($jadwal->guru, $jadwal);
                if ($result) {
                    $this->info("Notifikasi terkirim ke: {$jadwal->guru->nama_lengkap}");
                } else {
                    $this->error("Gagal mengirim notifikasi ke: {$jadwal->guru->nama_lengkap}");
                }
            } else {
                $this->warn("Guru {$jadwal->guru->nama_lengkap} belum menghubungkan Telegram");
            }
        }
        
        $this->info("Total notifikasi terkirim: {$jadwalList->count()}");
    }
}