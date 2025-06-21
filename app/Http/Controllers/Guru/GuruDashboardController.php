<?php
// app/Http/Controllers/Guru/GuruDashboardController.php


namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\JadwalPelajaran;
use Carbon\Carbon;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        $hari = Carbon::now()->locale('id')->dayName;
        
        // Jadwal hari ini
        $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_ke')
            ->get();
        
        // Statistik absensi bulan ini
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;
        
        $statistikAbsensi = AbsensiGuru::where('guru_id', $guru->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        
        $data = [
            'guru' => $guru,
            'jadwalHariIni' => $jadwalHariIni,
            'totalHadir' => $statistikAbsensi['hadir'] ?? 0,
            'totalIzin' => $statistikAbsensi['izin'] ?? 0,
            'totalSakit' => $statistikAbsensi['sakit'] ?? 0,
            'totalDinasLuar' => $statistikAbsensi['dinas_luar'] ?? 0,
            'totalCuti' => $statistikAbsensi['cuti'] ?? 0,
            'totalAlpa' => $statistikAbsensi['tidak_hadir'] ?? 0,
        ];

        return view('guru.dashboard', $data);
    }
}






// namespace App\Http\Controllers\Guru;

// use App\Http\Controllers\Controller;
// use App\Models\AbsensiGuru;
// use App\Models\JadwalPelajaran;
// use Carbon\Carbon;

// class GuruDashboardController extends Controller
// {
//     public function index()
//     {
//         $guru = auth()->user()->guru;
//         $hari = Carbon::now()->locale('id')->dayName;
        
//         // Jadwal hari ini
//         $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
//             ->where('guru_id', $guru->id)
//             ->where('hari', $hari)
//             ->orderBy('jam_ke')
//             ->get();
        
//         // Statistik absensi bulan ini
//         $bulanIni = Carbon::now()->month;
//         $tahunIni = Carbon::now()->year;
        
//         $statistikAbsensi = AbsensiGuru::where('guru_id', $guru->id)
//             ->whereMonth('tanggal', $bulanIni)
//             ->whereYear('tanggal', $tahunIni)
//             ->selectRaw('status, count(*) as total')
//             ->groupBy('status')
//             ->pluck('total', 'status')
//             ->toArray();
        
//         $data = [
//             'guru' => $guru,
//             'jadwalHariIni' => $jadwalHariIni,
//             'totalHadir' => $statistikAbsensi['hadir'] ?? 0,
//             'totalIzin' => $statistikAbsensi['izin'] ?? 0,
//             'totalSakit' => $statistikAbsensi['sakit'] ?? 0,
//             'totalAlpa' => $statistikAbsensi['tidak_hadir'] ?? 0,
//         ];

//         return view('guru.dashboard', $data);
//     }
// }