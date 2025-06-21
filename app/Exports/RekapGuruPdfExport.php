<?php

namespace App\Exports;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Carbon\Carbon;

class RekapGuruPdfExport
{
    protected $guruId;
    protected $filterParams;
    
    public function __construct($guruId, $filterParams)
    {
        $this->guruId = $guruId;
        $this->filterParams = $filterParams;
    }
    
    public function getData()
    {
        $guru = Guru::findOrFail($this->guruId);
        
        $query = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $this->guruId);
            
        // Apply date filter
        $periodeInfo = '';
        if ($this->filterParams['tanggal_mulai'] && $this->filterParams['tanggal_selesai']) {
            $query->whereBetween('tanggal', [$this->filterParams['tanggal_mulai'], $this->filterParams['tanggal_selesai']]);
            $periodeInfo = Carbon::parse($this->filterParams['tanggal_mulai'])->format('d/m/Y') . 
                          ' - ' . Carbon::parse($this->filterParams['tanggal_selesai'])->format('d/m/Y');
        }
        
        // Apply class filter
        $kelasInfo = null;
        if (!empty($this->filterParams['kelas_id'])) {
            $query->whereHas('jadwalPelajaran', function($q) {
                $q->where('kelas_id', $this->filterParams['kelas_id']);
            });
            $kelas = Kelas::find($this->filterParams['kelas_id']);
            $kelasInfo = $kelas ? $kelas->nama_kelas : null;
        }
        
        // Apply subject filter
        $mataPelajaranInfo = null;
        if (!empty($this->filterParams['mata_pelajaran_id'])) {
            $query->whereHas('jadwalPelajaran', function($q) {
                $q->where('mata_pelajaran_id', $this->filterParams['mata_pelajaran_id']);
            });
            $mataPelajaran = MataPelajaran::find($this->filterParams['mata_pelajaran_id']);
            $mataPelajaranInfo = $mataPelajaran ? $mataPelajaran->nama_mata_pelajaran : null;
        }
        
        $absensi = $query->orderBy('tanggal', 'desc')->get();
        
        $statistik = [
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'sakit' => $absensi->where('status', 'sakit')->count(),
            'dinas_luar' => $absensi->where('status', 'dinas_luar')->count(),
            'cuti' => $absensi->where('status', 'cuti')->count(),
            'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
        ];
        
        $totalData = $absensi->count();
        
        return compact('guru', 'absensi', 'statistik', 'totalData', 'periodeInfo', 'kelasInfo', 'mataPelajaranInfo');
    }
}

// namespace App\Exports;

// use App\Models\AbsensiGuru;
// use Carbon\Carbon;

// class RekapGuruPdfExport
// {
//     protected $guruId;
//     protected $bulan;
//     protected $tahun;

//     public function __construct($guruId, $bulan, $tahun)
//     {
//         $this->guruId = $guruId;
//         $this->bulan = $bulan;
//         $this->tahun = $tahun;
//     }

//     public function getData()
//     {
//         $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran', 'guru'])
//             ->where('guru_id', $this->guruId)
//             ->whereMonth('tanggal', $this->bulan)
//             ->whereYear('tanggal', $this->tahun)
//             ->orderBy('tanggal', 'desc')
//             ->get();

//         $statistik = [
//             'hadir' => $absensi->where('status', 'hadir')->count(),
//             'izin' => $absensi->where('status', 'izin')->count(),
//             'sakit' => $absensi->where('status', 'sakit')->count(),
//             'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
//         ];

//         $guru = $absensi->first()->guru ?? null;
//         $namaBulan = Carbon::create()->month($this->bulan)->locale('id')->monthName;

//         return [
//             'absensi' => $absensi,
//             'statistik' => $statistik,
//             'guru' => $guru,
//             'bulan' => $this->bulan,
//             'tahun' => $this->tahun,
//             'namaBulan' => $namaBulan,
//             'totalData' => $absensi->count()
//         ];
//     }
// }