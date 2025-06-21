<?php

// ============================================================================
// 6. COMMAND: PROCESS AUTO ALFA
// ============================================================================
// app/Console/Commands/ProcessAutoAlfa.php

namespace App\Console\Commands;

use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessAutoAlfa extends Command
{
    protected $signature = 'absensi:process-auto-alfa';
    protected $description = 'Process automatic alfa for teachers who missed attendance';
    
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }
    
    public function handle()
    {
        $currentTime = Carbon::now();
        $hari = $currentTime->locale('id')->dayName;
        $tanggal = $currentTime->toDateString();
        
        // Ambil jadwal yang sudah lewat 15 menit dari jam selesai dan belum diabsen
        $jadwalLewat = JadwalPelajaran::with(['guru', 'kelas', 'mataPelajaran'])
            ->where('hari', $hari)
            ->whereDoesntHave('absensiGuru', function ($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            })
            ->get();

        $autoAlfaCount = 0;
        
        foreach ($jadwalLewat as $jadwal) {
            try {
                if (empty($jadwal->jam_selesai)) {
                    continue;
                }

                $jamSelesai = $this->parseTimeFormat($jadwal->jam_selesai);
                if (!$jamSelesai) {
                    continue;
                }

                $jamSelesai->setDate($currentTime->year, $currentTime->month, $currentTime->day);
                
                // Jika sudah lewat 15 menit dari jam selesai
                if ($currentTime->isAfter($jamSelesai->addMinutes(1))) {
                    AbsensiGuru::create([
                        'guru_id' => $jadwal->guru_id,
                        'jadwal_pelajaran_id' => $jadwal->id,
                        'tanggal' => $tanggal,
                        'jam_absen' => null,
                        'status' => 'tidak_hadir',
                        'alasan' => 'Otomatis tidak hadir karena tidak melakukan absensi',
                        'tugas' => null,
                        'is_auto_alfa' => true
                    ]);

                    // Kirim notifikasi
                    $this->telegramService->sendAutoAlfaNotification($jadwal);
                    
                    $autoAlfaCount++;
                    $this->info("Auto alfa created for: {$jadwal->guru->nama_lengkap} - {$jadwal->mataPelajaran->nama_mata_pelajaran}");
                }
                
            } catch (\Exception $e) {
                $this->error("Error processing jadwal ID {$jadwal->id}: " . $e->getMessage());
                continue;
            }
        }
        
        $this->info("Total auto alfa processed: {$autoAlfaCount}");
    }
    
    private function parseTimeFormat($timeString)
    {
        if (empty($timeString)) {
            return null;
        }

        $timeString = trim($timeString);
        
        try {
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $timeString)) {
                return Carbon::createFromFormat('H:i', $timeString);
            } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeString)) {
                return Carbon::createFromFormat('H:i:s', $timeString);
            } elseif (strtotime($timeString)) {
                return Carbon::parse($timeString);
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
}






// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\JadwalPelajaran;
// use App\Models\AbsensiGuru;
// use App\Models\HariLibur;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;

// class ProcessAutoAlfa extends Command
// {
//     protected $signature = 'absensi:process-auto-alfa';
//     protected $description = 'Process automatic alfa for teachers who did not attend';

//     public function handle()
//     {
//         $today = Carbon::now();
//         $hari = $today->locale('id')->dayName;
//         $tanggal = $today->toDateString();

//         // Skip jika hari libur
//         if (HariLibur::isHariLibur($tanggal)) {
//             $this->info('Today is a holiday, skipping auto alfa processing.');
//             return;
//         }

//         $this->info("Processing auto alfa for date: {$tanggal}");

//         // Ambil semua jadwal yang sudah lewat dan belum diabsen
//         $jadwalLewat = JadwalPelajaran::with(['guru', 'kelas', 'mataPelajaran'])
//             ->where('hari', $hari)
//             ->whereNotExists(function ($query) use ($tanggal) {
//                 $query->select(DB::raw(1))
//                     ->from('absensi_guru')
//                     ->whereColumn('jadwal_pelajaran.id', 'absensi_guru.jadwal_pelajaran_id')
//                     ->whereDate('absensi_guru.tanggal', $tanggal);
//             })
//             ->get();

//         $processed = 0;

//         foreach ($jadwalLewat as $jadwal) {
//             $jamSelesai = Carbon::createFromFormat('H:i', $jadwal->jam_selesai);
            
//             // Jika sudah lewat 15 menit dari jam selesai, otomatis alfa
//             if ($today->isAfter($jamSelesai->addMinutes(15))) {
//                 AbsensiGuru::create([
//                     'guru_id' => $jadwal->guru_id,
//                     'jadwal_pelajaran_id' => $jadwal->id,
//                     'tanggal' => $tanggal,
//                     'jam_absen' => null,
//                     'status' => 'tidak_hadir',
//                     'alasan' => 'Otomatis tidak hadir karena tidak melakukan absensi',
//                     'tugas' => null,
//                     'is_auto_alfa' => true
//                 ]);

//                 $processed++;
//                 $this->info("Auto alfa processed for {$jadwal->guru->nama_lengkap} - {$jadwal->mataPelajaran->nama_mata_pelajaran}");
//             }
//         }

//         $this->info("Total processed: {$processed} records");
//     }
// }