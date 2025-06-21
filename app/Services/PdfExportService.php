<?php
// app/Services/PdfExportService.php

namespace App\Services;

use App\Models\AbsensiGuru;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfExportService
{
    public function exportAbsensi($filters)
    {
        $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->whereBetween('tanggal', [$filters['tanggal_mulai'], $filters['tanggal_akhir']]);
            
        if ($filters['guru_id']) {
            $query->where('guru_id', $filters['guru_id']);
        }
        
        if ($filters['kelas_id']) {
            $query->whereHas('jadwalPelajaran', function($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }
        
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        $data = $query->orderBy('tanggal', 'desc')->get();
        
        // Calculate statistics
        $statistik = [
            'total' => $data->count(),
            'hadir' => $data->where('status', 'hadir')->count(),
            'izin' => $data->where('status', 'izin')->count(),
            'sakit' => $data->where('status', 'sakit')->count(),
            'alpa' => $data->where('status', 'tidak_hadir')->count(),
        ];
        
        $pdf = Pdf::loadView('exports.absensi-pdf', [
            'data' => $data,
            'filters' => $filters,
            'statistik' => $statistik,
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM Y')
        ]);
        
        return $pdf->download('laporan-absensi.pdf');
    }
    
    public function exportRekapGuru($guruId, $bulan, $tahun)
    {
        $guru = \App\Models\Guru::findOrFail($guruId);
        
        $data = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $guruId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
            
        $statistik = [
            'hadir' => $data->where('status', 'hadir')->count(),
            'izin' => $data->where('status', 'izin')->count(),
            'sakit' => $data->where('status', 'sakit')->count(),
            'alpa' => $data->where('status', 'tidak_hadir')->count(),
        ];
        
        $pdf = Pdf::loadView('exports.rekap-guru-pdf', [
            'guru' => $guru,
            'data' => $data,
            'bulan' => Carbon::create()->month($bulan)->locale('id')->monthName,
            'tahun' => $tahun,
            'statistik' => $statistik,
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM Y')
        ]);
        
        return $pdf->download("rekap-absensi-{$guru->nama_lengkap}.pdf");
    }
}