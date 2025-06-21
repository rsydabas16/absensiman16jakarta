<?php
// app/Http/Controllers/Siswa/SiswaDashboardController.php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use Carbon\Carbon;

class SiswaDashboardController extends Controller
{
    public function index()
    {
        $siswa = auth()->user()->siswa;
        $hari = Carbon::now()->locale('id')->dayName;
        
        // Jadwal kelas hari ini
        $jadwalHariIni = JadwalPelajaran::with(['guru.user', 'mataPelajaran'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hari)
            ->orderBy('jam_ke')
            ->get();
            
        // Cek status absensi guru hari ini
        $tanggalHariIni = Carbon::now()->toDateString();
        $statusAbsensiGuru = [];
        
        foreach ($jadwalHariIni as $jadwal) {
            $absensi = AbsensiGuru::where('jadwal_pelajaran_id', $jadwal->id)
                ->where('tanggal', $tanggalHariIni)
                ->first();
                
            $statusAbsensiGuru[$jadwal->id] = $absensi;
        }
        
        $data = [
            'siswa' => $siswa,
            'jadwalHariIni' => $jadwalHariIni,
            'statusAbsensiGuru' => $statusAbsensiGuru,
        ];

        return view('siswa.dashboard', $data);
    }
}