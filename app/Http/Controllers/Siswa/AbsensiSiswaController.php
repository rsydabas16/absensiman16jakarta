<?php
// app/Http/Controllers/Siswa/AbsensiSiswaController.php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Siswa;
use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    public function index()
    {
        $siswa = auth()->user()->siswa;
        $tanggalHariIni = Carbon::now()->toDateString();
        
        // Cek apakah hari libur
        $isHariLibur = HariLibur::where('tanggal', $tanggalHariIni)->exists();
        
        if ($isHariLibur) {
            $hariLibur = HariLibur::where('tanggal', $tanggalHariIni)->first();
            return view('siswa.absensi-siswa.libur', compact('hariLibur'));
        }

        // Ambil semua siswa dalam kelas yang sama
        $daftarSiswa = Siswa::where('kelas_id', $siswa->kelas_id)
                           ->orderBy('nama_lengkap')
                           ->get();

        // Cek absensi yang sudah ada hari ini
        $absensiHariIni = AbsensiSiswa::where('kelas_id', $siswa->kelas_id)
                                    ->where('tanggal', $tanggalHariIni)
                                    ->get()
                                    ->keyBy('siswa_id');

        // Cek apakah sudah ada yang absen hari ini
        $sudahAbsen = $absensiHariIni->isNotEmpty();

        return view('siswa.absensi-siswa.index', compact(
            'daftarSiswa', 
            'absensiHariIni', 
            'sudahAbsen',
            'tanggalHariIni'
        ));
    }

    public function store(Request $request)
    {
        $siswa = auth()->user()->siswa;
        $tanggalHariIni = Carbon::now()->toDateString();

        $request->validate([
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alfa',
            'absensi.*.keterangan' => 'nullable|string|max:255'
        ]);

        try {
            // Hapus absensi sebelumnya untuk hari ini (jika ada)
            AbsensiSiswa::where('kelas_id', $siswa->kelas_id)
                       ->where('tanggal', $tanggalHariIni)
                       ->delete();

            // Simpan absensi baru
            foreach ($request->absensi as $siswaId => $data) {
                AbsensiSiswa::create([
                    'siswa_id' => $siswaId,
                    'kelas_id' => $siswa->kelas_id,
                    'tanggal' => $tanggalHariIni,
                    'status' => $data['status'],
                    'keterangan' => $data['keterangan'] ?? null,
                    'dicatat_oleh' => $siswa->id
                ]);
            }

            return redirect()->route('siswa.absensi-siswa.index')
                           ->with('success', 'Absensi siswa berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan absensi.');
        }
    }

    public function rekap()
    {
        $siswa = auth()->user()->siswa;
        
        // Ambil data rekap 30 hari terakhir
        $startDate = Carbon::now()->subDays(30)->toDateString();
        $endDate = Carbon::now()->toDateString();

        $rekapAbsensi = AbsensiSiswa::with(['siswa'])
                                  ->where('kelas_id', $siswa->kelas_id)
                                  ->whereBetween('tanggal', [$startDate, $endDate])
                                  ->orderBy('tanggal', 'desc')
                                  ->get()
                                  ->groupBy('tanggal');

        return view('siswa.absensi-siswa.rekap', compact('rekapAbsensi'));
    }
}