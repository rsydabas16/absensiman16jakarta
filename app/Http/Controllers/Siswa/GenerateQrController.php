<?php
// app/Http/Controllers/Siswa/GenerateQrController.php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use App\Services\QrScannerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQrController extends Controller
{
    protected $qrService;
    
    public function __construct(QrScannerService $qrService)
    {
        $this->qrService = $qrService;
    }
    
    public function index()
    {
        $siswa = auth()->user()->siswa;
        $hari = Carbon::now()->locale('id')->dayName;
        $tanggal = Carbon::now()->toDateString();
        
        // Jadwal kelas hari ini
        $jadwalHariIni = JadwalPelajaran::with(['guru.user', 'mataPelajaran'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hari)
            ->orderBy('jam_ke')
            ->get();
            
        // Cek status absensi guru
        $statusAbsensi = [];
        foreach ($jadwalHariIni as $jadwal) {
            $absensi = AbsensiGuru::where('jadwal_pelajaran_id', $jadwal->id)
                ->whereDate('tanggal', $tanggal)
                ->first();
            $statusAbsensi[$jadwal->id] = $absensi;
        }
        
        return view('siswa.generate-qr.index', compact('jadwalHariIni', 'statusAbsensi', 'tanggal'));
    }
    
    public function create(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_pelajaran,id'
        ]);
        
        $jadwal = JadwalPelajaran::with(['guru.user', 'mataPelajaran'])
            ->findOrFail($request->jadwal_id);
            
        // Validasi jadwal milik kelas siswa
        if ($jadwal->kelas_id != auth()->user()->siswa->kelas_id) {
            return back()->with('error', 'Jadwal tidak valid untuk kelas Anda.');
        }
        
        // Cek apakah guru sudah absen
        $sudahAbsen = AbsensiGuru::where('jadwal_pelajaran_id', $jadwal->id)
            ->whereDate('tanggal', now()->toDateString())
            ->exists();
            
        if ($sudahAbsen) {
            return back()->with('error', 'Guru sudah melakukan absensi untuk jadwal ini.');
        }
        
        // Generate QR Code
        $qrContent = $this->qrService->generateQrCode($jadwal->id);
        $qrImage = QrCode::size(300)->generate($qrContent);
        
        return view('siswa.generate-qr.show', compact('jadwal', 'qrImage', 'qrContent'));
    }
    
    public function regenerate(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_pelajaran,id'
        ]);
        
        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_id);
        
        // Generate QR baru
        $qrContent = $this->qrService->generateQrCode($jadwal->id);
        
        return response()->json([
            'success' => true,
            'qr_content' => $qrContent,
            'qr_image' => base64_encode(QrCode::size(300)->generate($qrContent))
        ]);
    }
}