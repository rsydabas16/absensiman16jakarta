<?php
// app/Http/Controllers/Siswa/MateriController.php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\MateriPembelajaran;
use App\Models\JadwalPelajaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    public function index()
    {
        $siswa = auth()->user()->siswa;
        $hari = Carbon::now()->locale('id')->dayName;
        $tanggal = Carbon::now()->toDateString();
        
        // Jadwal yang sudah diabsen hari ini
        $jadwalHariIni = JadwalPelajaran::with(['guru.user', 'mataPelajaran'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hari)
            ->whereHas('absensiGuru', function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            })
            ->orderBy('jam_ke')
            ->get();
        
        // Materi yang sudah diisi
        $materiTerisi = MateriPembelajaran::where('siswa_id', $siswa->id)
            ->whereHas('absensiGuru', function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            })
            ->pluck('absensi_guru_id')
            ->toArray();
        
        return view('siswa.materi.index', compact('jadwalHariIni', 'materiTerisi'));
    }
    
    public function create(Request $request)
    {
        $request->validate([
            'absensi_id' => 'required|exists:absensi_guru,id'
        ]);
        
        $absensi = AbsensiGuru::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.guru'])
            ->findOrFail($request->absensi_id);
        
        // Validasi kelas
        if ($absensi->jadwalPelajaran->kelas_id != auth()->user()->siswa->kelas_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengisi materi ini.');
        }
        
        // Cek apakah sudah mengisi
        $sudahIsi = MateriPembelajaran::where('absensi_guru_id', $absensi->id)
            ->where('siswa_id', auth()->user()->siswa->id)
            ->exists();
            
        if ($sudahIsi) {
            return back()->with('error', 'Anda sudah mengisi materi untuk pertemuan ini.');
        }
        
        return view('siswa.materi.create', compact('absensi'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'absensi_guru_id' => 'required|exists:absensi_guru,id',
            'materi' => 'required|min:10'
        ]);
        
        $absensi = AbsensiGuru::findOrFail($request->absensi_guru_id);
        
        // Validasi kelas
        if ($absensi->jadwalPelajaran->kelas_id != auth()->user()->siswa->kelas_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengisi materi ini.');
        }
        
        MateriPembelajaran::create([
            'absensi_guru_id' => $request->absensi_guru_id,
            'siswa_id' => auth()->user()->siswa->id,
            'materi' => $request->materi
        ]);
        
        return redirect()->route('siswa.materi.index')
            ->with('success', 'Materi pembelajaran berhasil disimpan.');
    }
    
    public function tugasGuru()
    {
        $siswa = auth()->user()->siswa;
        $tanggal = Carbon::now()->toDateString();
        
        // Tugas dari guru yang tidak hadir
        $tugasList = AbsensiGuru::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.guru'])
            ->whereHas('jadwalPelajaran', function($q) use ($siswa) {
                $q->where('kelas_id', $siswa->kelas_id);
            })
            ->where('status', 'tidak_hadir')
            ->whereNotNull('tugas')
            ->whereDate('tanggal', '>=', Carbon::now()->subDays(7)) // 7 hari terakhir
            ->orderBy('tanggal', 'desc')
            ->get();
        
        return view('siswa.materi.tugas', compact('tugasList'));
    }
}