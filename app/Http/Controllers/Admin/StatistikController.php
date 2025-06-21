<?php
// app/Http/Controllers/KepalaSekolah/StatistikController.php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\AbsensiGuru;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->periode ?? 'bulanan';
        $bulan = $request->bulan ?? Carbon::now()->month;
        $tahun = $request->tahun ?? Carbon::now()->year;

        // Get all teachers
        $guruList = Guru::orderBy('nama_lengkap')->get();

        // Calculate statistics for each teacher
        $statistikGuru = [];
        
        foreach ($guruList as $guru) {
            $query = AbsensiGuru::where('guru_id', $guru->id);
            
            switch ($periode) {
                case 'harian':
                    $tanggal = $request->tanggal ?? Carbon::now()->toDateString();
                    $query->whereDate('tanggal', $tanggal);
                    break;
                    
                case 'mingguan':
                    $startOfWeek = Carbon::now()->startOfWeek();
                    $endOfWeek = Carbon::now()->endOfWeek();
                    if ($request->start_date && $request->end_date) {
                        $startOfWeek = Carbon::parse($request->start_date);
                        $endOfWeek = Carbon::parse($request->end_date);
                    }
                    $query->whereBetween('tanggal', [$startOfWeek, $endOfWeek]);
                    break;
                    
                case 'bulanan':
                default:
                    $query->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun);
                    break;
            }
            
            $absensi = $query->get();
            $total = $absensi->count();
            
            if ($total > 0) {
                $hadir = $absensi->where('status', 'hadir')->count();
                $izin = $absensi->where('status', 'izin')->count();
                $sakit = $absensi->where('status', 'sakit')->count();
                $alpa = $absensi->where('status', 'tidak_hadir')->count();
                
                $statistikGuru[] = [
                    'guru' => $guru,
                    'total' => $total,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'persentase_hadir' => round(($hadir / $total) * 100, 2),
                    'chart_data' => [
                        ['name' => 'Hadir', 'y' => $hadir, 'color' => '#28a745'],
                        ['name' => 'Izin', 'y' => $izin, 'color' => '#17a2b8'],
                        ['name' => 'Sakit', 'y' => $sakit, 'color' => '#ffc107'],
                        ['name' => 'Alpa', 'y' => $alpa, 'color' => '#dc3545'],
                    ]
                ];
            }
        }

        // Sort by attendance percentage
        usort($statistikGuru, function($a, $b) {
            return $b['persentase_hadir'] <=> $a['persentase_hadir'];
        });

        return view('kepala-sekolah.statistik.index', compact(
            'statistikGuru', 'periode', 'bulan', 'tahun'
        ));
    }

    public function detail($guruId, Request $request)
    {
        $guru = Guru::findOrFail($guruId);
        $bulan = $request->bulan ?? Carbon::now()->month;
        $tahun = $request->tahun ?? Carbon::now()->year;

        // Get absensi data for the month
        $absensiData = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $guruId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Weekly breakdown
        $weeklyData = $this->getWeeklyBreakdown($guruId, $bulan, $tahun);

        // Per subject statistics
        $perMapelData = $this->getPerMapelStatistics($guruId, $bulan, $tahun);

        return view('kepala-sekolah.statistik.detail', compact(
            'guru', 'absensiData', 'weeklyData', 'perMapelData', 'bulan', 'tahun'
        ));
    }

    private function getWeeklyBreakdown($guruId, $bulan, $tahun)
    {
        $startOfMonth = Carbon::create($tahun, $bulan, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $weeks = [];
        $current = $startOfMonth->copy()->startOfWeek();
        
        while ($current <= $endOfMonth) {
            $weekEnd = $current->copy()->endOfWeek();
            
            if ($weekEnd > $endOfMonth) {
                $weekEnd = $endOfMonth;
            }
            
            $absensi = AbsensiGuru::where('guru_id', $guruId)
                ->whereBetween('tanggal', [$current, $weekEnd])
                ->get();
                
            $total = $absensi->count();
            $hadir = $absensi->where('status', 'hadir')->count();
            
            $weeks[] = [
                'week' => $current->weekOfMonth,
                'start' => $current->format('d M'),
                'end' => $weekEnd->format('d M'),
                'total' => $total,
                'hadir' => $hadir,
                'persentase' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0
            ];
            
            $current->addWeek();
        }
        
        return $weeks;
    }

    private function getPerMapelStatistics($guruId, $bulan, $tahun)
    {
        $data = AbsensiGuru::with(['jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $guruId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->groupBy('jadwalPelajaran.mata_pelajaran_id');
            
        $result = [];
        
        foreach ($data as $mapelId => $absensiCollection) {
            $mapel = $absensiCollection->first()->jadwalPelajaran->mataPelajaran;
            $total = $absensiCollection->count();
            $hadir = $absensiCollection->where('status', 'hadir')->count();
            
            $result[] = [
                'mata_pelajaran' => $mapel->nama_mata_pelajaran,
                'total' => $total,
                'hadir' => $hadir,
                'persentase' => round(($hadir / $total) * 100, 2)
            ];
        }
        
        return $result;
    }
}