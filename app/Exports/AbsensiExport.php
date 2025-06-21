<?php
// app/Exports/AbsensiExport.php

namespace App\Exports;

use App\Models\AbsensiGuru;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsensiExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $filters;
    
    public function __construct($filters)
    {
        $this->filters = $filters;
    }
    
    public function query()
    {
        $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->whereBetween('tanggal', [$this->filters['tanggal_mulai'], $this->filters['tanggal_akhir']]);
            
        if ($this->filters['guru_id']) {
            $query->where('guru_id', $this->filters['guru_id']);
        }
        
        if ($this->filters['kelas_id']) {
            $query->whereHas('jadwalPelajaran', function($q) {
                $q->where('kelas_id', $this->filters['kelas_id']);
            });
        }
        
        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }
        
        return $query->orderBy('tanggal', 'desc');
    }
    
    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Guru',
            'NIP',
            'Mata Pelajaran',
            'Kelas',
            'Jam Ke',
            'Waktu',
            'Status',
            'Jam Absen',
            'Alasan',
            'Tugas'
        ];
    }
    
    public function map($absensi): array
    {
        return [
            $absensi->tanggal->format('d/m/Y'),
            $absensi->tanggal->locale('id')->dayName,
            $absensi->guru->nama_lengkap,
            $absensi->guru->nip,
            $absensi->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran,
            $absensi->jadwalPelajaran->kelas->nama_kelas,
            $absensi->jadwalPelajaran->jam_ke,
            $absensi->jadwalPelajaran->jam_mulai . ' - ' . $absensi->jadwalPelajaran->jam_selesai,
            ucfirst($absensi->status),
            $absensi->jam_absen ?? '-',
            $absensi->alasan ?? '-',
            $absensi->tugas ?? '-'
        ];
    }
    
    public function title(): string
    {
        return 'Laporan Absensi';
    }
}