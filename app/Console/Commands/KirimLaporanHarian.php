<?php
// app/Console/Commands/KirimLaporanHarian.php

// ============================================================================
// 5. COMMAND: LAPORAN HARIAN
// ============================================================================
// app/Console/Commands/KirimLaporanHarian.php

namespace App\Console\Commands;

use App\Models\AbsensiGuru;
use App\Models\JadwalPelajaran;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class KirimLaporanHarian extends Command
{
    protected $signature = 'laporan:harian';
    protected $description = 'Kirim laporan absensi harian';
    
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }
    
    public function handle()
    {
        $tanggal = Carbon::now()->toDateString();
        $hari = Carbon::now()->locale('id')->dayName;
        
        // Hitung total jadwal hari ini
        $totalJadwal = JadwalPelajaran::where('hari', $hari)->count();
        
        // Statistik absensi
        $absensiHariIni = AbsensiGuru::whereDate('tanggal', $tanggal)->get();
        
        $statistik = [
            'tanggal' => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y'),
            'total_jadwal' => $totalJadwal,
            'hadir' => $absensiHariIni->where('status', 'hadir')->count(),
            'tidak_hadir' => $absensiHariIni->where('status', 'tidak_hadir')->count(),
            'izin' => $absensiHariIni->where('status', 'izin')->count(),
            'sakit' => $absensiHariIni->where('status', 'sakit')->count(),
            'dinas_luar' => $absensiHariIni->where('status', 'dinas_luar')->count(),
            'cuti' => $absensiHariIni->where('status', 'cuti')->count(),
            'auto_alfa' => $absensiHariIni->where('is_auto_alfa', true)->count(),
        ];
        
        $result = $this->telegramService->sendDailyReport($statistik);
        
        if ($result) {
            $this->info('Laporan harian berhasil dikirim');
        } else {
            $this->error('Gagal mengirim laporan harian');
        }
    }
}





// namespace App\Console\Commands;

// use App\Models\AbsensiGuru;
// use App\Models\Guru;
// use App\Models\User;
// use App\Services\TelegramService;
// use Carbon\Carbon;
// use Illuminate\Console\Command;

// class KirimLaporanHarian extends Command
// {
//     protected $signature = 'laporan:harian';
//     protected $description = 'Kirim laporan harian absensi ke kepala sekolah';
    
//     protected $telegramService;
    
//     public function __construct(TelegramService $telegramService)
//     {
//         parent::__construct();
//         $this->telegramService = $telegramService;
//     }
    
//     public function handle()
//     {
//         $tanggal = Carbon::now()->toDateString();
        
//         // Hitung statistik
//         $totalGuru = Guru::count();
//         $absensiHariIni = AbsensiGuru::whereDate('tanggal', $tanggal)->get();
        
//         $data = [
//             'total_guru' => $totalGuru,
//             'hadir' => $absensiHariIni->where('status', 'hadir')->count(),
//             'izin' => $absensiHariIni->where('status', 'izin')->count(),
//             'sakit' => $absensiHariIni->where('status', 'sakit')->count(),
//             'alpa' => $absensiHariIni->where('status', 'tidak_hadir')->count(),
//         ];
        
//         $data['persentase'] = $totalGuru > 0 
//             ? round(($data['hadir'] / $totalGuru) * 100, 2) 
//             : 0;
        
//         // Kirim ke kepala sekolah
//         $kepalaSekolah = User::where('role', 'kepala_sekolah')
//             ->whereNotNull('telegram_chat_id')
//             ->get();
            
//         foreach ($kepalaSekolah as $kepsek) {
//             $this->telegramService->sendDailyReport($kepsek, $data);
//         }
        
//         $this->info('Laporan harian telah dikirim.');
//     }
// }