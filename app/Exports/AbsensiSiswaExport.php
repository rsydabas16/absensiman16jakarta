<?php
// app/Exports/AbsensiSiswaExport.php

namespace App\Exports;

use App\Models\AbsensiSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiSiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = AbsensiSiswa::with(['siswa', 'kelas', 'pencatat']);

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->where('tanggal', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->where('tanggal', '<=', $this->filters['end_date']);
        }
        if (!empty($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'NISN', 
            'Nama Siswa',
            'Kelas',
            'Status',
            'Keterangan',
            'Dicatat Oleh',
            'Waktu Pencatatan'
        ];
    }

    public function map($absensi): array
    {
        static $no = 1;
        
        return [
            $no++,
            $absensi->tanggal->format('d/m/Y'),
            $absensi->siswa->nisn,
            $absensi->siswa->nama_lengkap,
            $absensi->kelas->nama_kelas,
            ucfirst($absensi->status),
            $absensi->keterangan ?? '-',
            $absensi->pencatat->nama_lengkap,
            $absensi->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]]
        ];
    }
}