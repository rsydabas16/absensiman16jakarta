<?php
// app/Http/Controllers/Admin/JadwalPelajaranController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JadwalPelajaranImport;

class JadwalPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalPelajaran::with(['guru', 'kelas', 'mataPelajaran']);
        
        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }
        
        if ($request->hari) {
            $query->where('hari', $request->hari);
        }
        
        $jadwal = $query->orderBy('hari')
                       ->orderBy('jam_ke')
                       ->get();
        
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        return view('admin.jadwal-pelajaran.index', compact('jadwal', 'kelasList', 'hariList'));
    }
    
    public function create()
    {
        $guruList = Guru::orderBy('nama_lengkap')->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mata_pelajaran')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $semesterList = ['ganjil', 'genap']; // atau ambil dari model jika perlu
        
        return view('admin.jadwal-pelajaran.create', compact(
            'guruList', 'kelasList', 'mataPelajaranList', 'hariList','semesterList'
        ));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
        ]);
        
        // Check for conflicts
        $conflict = JadwalPelajaran::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('jam_ke', $request->jam_ke)
            ->exists();
            
        if ($conflict) {
            return back()->with('error', 'Jadwal bentrok! Sudah ada jadwal pada hari dan jam yang sama.');
        }
        
        JadwalPelajaran::create($request->all());
        
        return redirect()->route('admin.jadwal-pelajaran.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }
    
    public function edit(JadwalPelajaran $jadwalPelajaran)
    {
        $guruList = Guru::orderBy('nama_lengkap')->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mata_pelajaran')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        return view('admin.jadwal-pelajaran.edit', compact(
            'jadwalPelajaran', 'guruList', 'kelasList', 'mataPelajaranList', 'hariList'
        ));
    }
    
    public function update(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
        ]);
        
        // Check for conflicts (excluding current schedule)
        $conflict = JadwalPelajaran::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('jam_ke', $request->jam_ke)
            ->where('id', '!=', $jadwalPelajaran->id)
            ->exists();
            
        if ($conflict) {
            return back()->with('error', 'Jadwal bentrok! Sudah ada jadwal pada hari dan jam yang sama.');
        }
        
        $jadwalPelajaran->update($request->all());
        
        return redirect()->route('admin.jadwal-pelajaran.index')
            ->with('success', 'Jadwal berhasil diupdate.');
    }
    
    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        try {
            $jadwalPelajaran->delete();
            return redirect()->route('admin.jadwal-pelajaran.index')
                ->with('success', 'Jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Jadwal tidak bisa dihapus karena masih memiliki data terkait.');
        }
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        
        try {
            Excel::import(new JadwalPelajaranImport, $request->file('file'));
            return back()->with('success', 'Data jadwal berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}