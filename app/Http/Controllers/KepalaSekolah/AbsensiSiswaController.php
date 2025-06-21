<?php
// app/Http/Controllers/KepalaSekolah/AbsensiSiswaController.php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiSiswaExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsensiSiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = AbsensiSiswa::with(['siswa', 'kelas', 'pencatat']);

        // Filter berdasarkan tanggal (default 30 hari terakhir)
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->toDateString();
        
        $query->whereBetween('tanggal', [$startDate, $endDate]);

        // Filter berdasarkan kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $absensiSiswa = $query->orderBy('tanggal', 'desc')
                            ->orderBy('kelas_id')
                            ->paginate(20);

        // Data untuk filter dan statistik
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        
        // // Statistik per status
        // $statistik = AbsensiSiswa::selectRaw('status, COUNT(*) as jumlah')
        //                         ->whereBetween('tanggal', [$startDate, $endDate])
        //                         ->when($request->filled('kelas_id'), function($q) use ($request) {
        //                             return $q->where('kelas_id', $request->kelas_id);
        //                         })
        //                         ->groupBy('status')
        //                         ->pluck('jumlah', 'status')
        //                         ->toArray();

        // // Statistik per kelas
        // $statistikKelas = AbsensiSiswa::with('kelas')
        //                             ->selectRaw('kelas_id, status, COUNT(*) as jumlah')
        //                             ->whereBetween('tanggal', [$startDate, $endDate])
        //                             ->groupBy('kelas_id', 'status')
        //                             ->get()
        //                             ->groupBy('kelas_id');

      
      
        // return view('kepala_sekolah.absensi-siswa.index', compact(
        //     'absensiSiswa',
        //     'kelasList',
        //     'statistik',
        //     'statistikKelas',
        //     'startDate',
        //     'endDate'
        // ));

          // Statistik
        $totalHadir = AbsensiSiswa::where('status', 'hadir')->count();
        $totalIzin = AbsensiSiswa::where('status', 'izin')->count();
        $totalSakit = AbsensiSiswa::where('status', 'sakit')->count();
        $totalAlfla = AbsensiSiswa::where('status', 'alfa')->count();

        return view('kepala_sekolah.absensi-siswa.index', compact(
            'absensiSiswa',
            'kelasList',
            'totalHadir',
            'totalIzin', 
            'totalSakit',
            'totalAlfla'
        ));
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'kelas_id' => $request->kelas_id,
            'status' => $request->status
        ];

        return Excel::download(new AbsensiSiswaExport($filters), 'laporan_absensi_siswa_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = AbsensiSiswa::with(['siswa', 'kelas', 'pencatat']);

        // Apply filters
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->toDateString();
        
        $query->whereBetween('tanggal', [$startDate, $endDate]);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $absensiSiswa = $query->orderBy('tanggal', 'desc')->get();

        $pdf = Pdf::loadView('kepala_sekolah.absensi-siswa.pdf', compact('absensiSiswa', 'startDate', 'endDate'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('laporan_absensi_siswa_' . date('Y-m-d') . '.pdf');
    }
}