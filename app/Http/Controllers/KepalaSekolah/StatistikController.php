<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        // Default periode dan tanggal
        $periode = $request->periode ?? 'bulanan';
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        $tanggal = $request->tanggal ?? now()->toDateString();
        
        // Query guru
        $guru = Guru::all();
        
        // Array untuk menyimpan statistik guru
        $statistikGuru = [];
        
        foreach ($guru as $g) {
            // Query absensi berdasarkan periode
            $query = AbsensiGuru::where('guru_id', $g->id);
            
            if ($periode == 'harian') {
                $query->whereDate('tanggal', $tanggal);
            } elseif ($periode == 'mingguan') {
                $startDate = $request->start_date ?? now()->startOfWeek()->toDateString();
                $endDate = $request->end_date ?? now()->endOfWeek()->toDateString();
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($periode == 'bulanan') {
                $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            }
            
            $absensi = $query->get();
            
            // Hitung statistik
            $hadir = $absensi->where('status', 'hadir')->count();
            $izin = $absensi->where('status', 'izin')->count();
            $sakit = $absensi->where('status', 'sakit')->count();
            $dinas_luar = $absensi->where('status', 'dinas_luar')->count();
            $cuti = $absensi->where('status', 'cuti')->count();
            $alpa = $absensi->where('status', 'tidak_hadir')->count();
            $total = $absensi->count();
            
            // Hitung persentase kehadiran
            $persentaseHadir = $total > 0 ? round(($hadir / $total) * 100) : 0;
            
            // Data untuk chart
            $chartData = [
                ['label' => 'Hadir', 'y' => $hadir, 'color' => '#28a745'],
                ['label' => 'Izin', 'y' => $izin, 'color' => '#17a2b8'],
                ['label' => 'Sakit', 'y' => $sakit, 'color' => '#ffc107'],
                ['label' => 'Dinas Luar', 'y' => $dinas_luar, 'color' => '#a10de0'],
                ['label' => 'Cuti', 'y' => $cuti, 'color' => '#cc7110'],
                ['label' => 'Alpa', 'y' => $alpa, 'color' => '#dc3545']
            ];
            
            // Tambahkan ke array statistik
            $statistikGuru[] = [
                'guru' => $g,
                'total' => $total,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'dinas_luar' => $dinas_luar,
                'cuti' => $cuti,
                'alpa' => $alpa,
                'persentase_hadir' => $persentaseHadir,
                'chart_data' => $chartData
            ];
        }
        
        return view('kepala_sekolah.statistik.index', compact(
            'statistikGuru', 
            'periode', 
            'bulan', 
            'tahun'
        ));
    }
    
    public function detail(Request $request, $guru)
    {
        // Ambil data guru
        $guru = Guru::findOrFail($guru);
        
        // Default filter
        $bulan = $request->bulan ?? date('n');
        $tahun = $request->tahun ?? date('Y');
        
        // Set tanggal awal dan akhir bulan
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        
        // Ambil data absensi
        $absensiData = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();
        
        // Data per minggu
        $weeklyData = [];
        $currentDate = clone $startDate;
        $weekNumber = 1;
        
        while ($currentDate <= $endDate) {
            $weekStart = clone $currentDate;
            $weekEnd = (clone $currentDate)->endOfWeek();
            
            if ($weekEnd > $endDate) {
                $weekEnd = clone $endDate;
            }
            
            $absensiMinggu = $absensiData->filter(function ($item) use ($weekStart, $weekEnd) {
                return $item->tanggal >= $weekStart && $item->tanggal <= $weekEnd;
            });
            
            $totalMinggu = $absensiMinggu->count();
            $hadirMinggu = $absensiMinggu->where('status', 'hadir')->count();
            $persentaseMinggu = $totalMinggu > 0 ? round(($hadirMinggu / $totalMinggu) * 100) : 0;
            
            $weeklyData[] = [
                'week' => $weekNumber,
                'start' => $weekStart->format('d/m/Y'),
                'end' => $weekEnd->format('d/m/Y'),
                'total' => $totalMinggu,
                'hadir' => $hadirMinggu,
                'persentase' => $persentaseMinggu
            ];
            
            $currentDate->addWeek();
            $weekNumber++;
        }
        
        // Data per mata pelajaran
        $perMapelData = [];
        $mapelIds = $absensiData->pluck('jadwal_pelajaran.mata_pelajaran_id')->unique();
        
        foreach ($mapelIds as $mapelId) {
            $absensiMapel = $absensiData->filter(function ($item) use ($mapelId) {
                return $item->jadwalPelajaran->mata_pelajaran_id == $mapelId;
            });
            
            if ($absensiMapel->isNotEmpty()) {
                $namaMapel = $absensiMapel->first()->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran;
                $totalMapel = $absensiMapel->count();
                $hadirMapel = $absensiMapel->where('status', 'hadir')->count();
                $persentaseMapel = round(($hadirMapel / $totalMapel) * 100);
                
                $perMapelData[] = [
                    'mata_pelajaran' => $namaMapel,
                    'total' => $totalMapel,
                    'hadir' => $hadirMapel,
                    'persentase' => $persentaseMapel
                ];
            }
        }
        
        return view('kepala_sekolah.statistik.detail', compact(
            'guru',
            'bulan',
            'tahun',
            'absensiData',
            'weeklyData',
            'perMapelData'
        ));
    }
}