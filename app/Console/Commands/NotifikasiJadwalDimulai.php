<?php
// ============================================================================
// 1. COMMAND: NOTIFIKASI JADWAL MENGAJAR DIMULAI
// ============================================================================
// app/Console/Commands/NotifikasiJadwalDimulai.php

namespace App\Console\Commands;

use App\Models\JadwalPelajaran;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifikasiJadwalDimulai extends Command
{
    protected $signature = 'notifikasi:jadwal-dimulai';
    protected $description = 'Kirim notifikasi ketika jadwal mengajar dimulai';
    
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
        $waktuSekarang = $now->format('H:i:00');
        
        // Cari jadwal yang dimulai sekarang
        $jadwalList = JadwalPelajaran::with(['guru.user', 'kelas', 'mataPelajaran'])
            ->where('hari', $hari)
            ->where('jam_mulai', $waktuSekarang)
            ->get();
            
        $this->info("Mencari jadwal yang dimulai pada hari {$hari} jam {$waktuSekarang}");
        $this->info("Ditemukan {$jadwalList->count()} jadwal");
            
        foreach ($jadwalList as $jadwal) {
            if ($jadwal->guru && $jadwal->guru->user && $jadwal->guru->user->telegram_chat_id) {
                $result = $this->telegramService->sendJadwalDimulaiNotification($jadwal->guru, $jadwal);
                if ($result) {
                    $this->info("Notifikasi jadwal dimulai terkirim ke: {$jadwal->guru->nama_lengkap}");
                } else {
                    $this->error("Gagal mengirim notifikasi jadwal dimulai ke: {$jadwal->guru->nama_lengkap}");
                }
            } else {
                $this->warn("Guru {$jadwal->guru->nama_lengkap} belum menghubungkan Telegram");
            }
        }
        
        $this->info("Total notifikasi jadwal dimulai terkirim: {$jadwalList->count()}");
    }
}