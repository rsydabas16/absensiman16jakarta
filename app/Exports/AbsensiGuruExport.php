<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsensiGuruExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $data;
    protected $title;
    protected $summary;
    protected $startDate;
    protected $endDate;
    
    public function __construct($data, $title, $summary, $startDate, $endDate)
    {
        $this->data = $data;
        $this->title = $title;
        $this->summary = $summary;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function collection()
    {
        return $this->data;
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
            'Status',
            'Jam Absen',
            'Keterangan'
        ];
    }
    
    public function map($absensi): array
    {
        return [
            $absensi->tanggal->format('d/m/Y'),
            $absensi->tanggal->locale('id')->dayName,
            $absensi->guru->nama_lengkap,
            $absensi->guru->nip ?? '-',
            $absensi->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran,
            $absensi->jadwalPelajaran->kelas->nama_kelas,
            $absensi->jadwalPelajaran->jam_ke,
            ucfirst($absensi->status),
            $absensi->jam_absen ?? '-',
            $absensi->status !== 'hadir' ? $absensi->alasan : '-'
        ];
    }
    
    public function title(): string
    {
        return 'Laporan Absensi';
    }
}