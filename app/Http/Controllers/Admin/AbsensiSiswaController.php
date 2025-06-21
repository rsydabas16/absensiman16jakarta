<?php
// app/Http/Controllers/Admin/AbsensiSiswaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
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

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        // Filter berdasarkan kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensiSiswa = $query->orderBy('tanggal', 'desc')
                            ->orderBy('kelas_id')
                            ->paginate(20);

        // Data untuk filter
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        
        // Statistik
        $totalHadir = AbsensiSiswa::where('status', 'hadir')->count();
        $totalIzin = AbsensiSiswa::where('status', 'izin')->count();
        $totalSakit = AbsensiSiswa::where('status', 'sakit')->count();
        $totalAlfla = AbsensiSiswa::where('status', 'alfa')->count();

        return view('admin.absensi-siswa.index', compact(
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

        return Excel::download(new AbsensiSiswaExport($filters), 'absensi_siswa_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = AbsensiSiswa::with(['siswa', 'kelas', 'pencatat']);

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensiSiswa = $query->orderBy('tanggal', 'desc')->get();

        $pdf = Pdf::loadView('admin.absensi-siswa.pdf', compact('absensiSiswa'));
        
        return $pdf->download('laporan_absensi_siswa_' . date('Y-m-d') . '.pdf');
    }
}