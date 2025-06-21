<?php
// app/Exports/RekapGuruExport.php
namespace App\Exports;

use App\Models\AbsensiGuru;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class RekapGuruExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $guruId;
    protected $filterParams;
    
    public function __construct($guruId, $filterParams)
    {
        $this->guruId = $guruId;
        $this->filterParams = $filterParams;
    }
    
    public function query()
    {
        $query = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $this->guruId);
            
        // Apply date filter
        if ($this->filterParams['tanggal_mulai'] && $this->filterParams['tanggal_selesai']) {
            $query->whereBetween('tanggal', [$this->filterParams['tanggal_mulai'], $this->filterParams['tanggal_selesai']]);
        }
        
        // Apply class filter
        if (!empty($this->filterParams['kelas_id'])) {
            $query->whereHas('jadwalPelajaran', function($q) {
                $q->where('kelas_id', $this->filterParams['kelas_id']);
            });
        }
        
        // Apply subject filter
        if (!empty($this->filterParams['mata_pelajaran_id'])) {
            $query->whereHas('jadwalPelajaran', function($q) {
                $q->where('mata_pelajaran_id', $this->filterParams['mata_pelajaran_id']);
            });
        }
        
        return $query->orderBy('tanggal', 'desc');
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'Jam Ke',
            'Mata Pelajaran',
            'Kelas',
            'Status',
            'Jam Absen',
            'Keterangan',
            'Tugas'
        ];
    }
    
    public function map($absensi): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter,
            $absensi->tanggal->format('d/m/Y'),
            $absensi->tanggal->locale('id')->dayName,
            $absensi->jadwalPelajaran->jam_ke,
            $absensi->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran,
            $absensi->jadwalPelajaran->kelas->nama_kelas,
            ucfirst($absensi->status),
            $absensi->jam_absen ?? '-',
            $absensi->status !== 'hadir' ? $absensi->alasan : '-',
            $absensi->tugas ? 'Ada Tugas' : '-'
        ];
    }
    
    public function title(): string
    {
        return 'Rekap Absensi';
    }
}

// namespace App\Exports;

// use App\Models\AbsensiGuru;
// use Maatwebsite\Excel\Concerns\FromQuery;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithTitle;

// class RekapGuruExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
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
    
//     public function query()
//     {
//         return AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
//             ->where('guru_id', $this->guruId)
//             ->whereMonth('tanggal', $this->bulan)
//             ->whereYear('tanggal', $this->tahun)
//             ->orderBy('tanggal', 'desc');
//     }
    
//     public function headings(): array
//     {
//         return [
//             'Tanggal',
//             'Hari',
//             'Jam Ke',
//             'Mata Pelajaran',
//             'Kelas',
//             'Status',
//             'Jam Absen',
//             'Keterangan'
//         ];
//     }
    
//     public function map($absensi): array
//     {
//         return [
//             $absensi->tanggal->format('d/m/Y'),
//             $absensi->tanggal->locale('id')->dayName,
//             $absensi->jadwalPelajaran->jam_ke,
//             $absensi->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran,
//             $absensi->jadwalPelajaran->kelas->nama_kelas,
//             ucfirst($absensi->status),
//             $absensi->jam_absen ?? '-',
//             $absensi->status !== 'hadir' ? $absensi->alasan : '-'
//         ];
//     }
    
//     public function title(): string
//     {
//         return 'Rekap Absensi';
//     }
// }