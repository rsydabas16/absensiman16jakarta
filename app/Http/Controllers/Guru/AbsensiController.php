<?php
// app/Http/Controllers/Guru/AbsensiController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\JadwalPelajaran;
use App\Models\HariLibur;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\RekapGuruExport;
use App\Exports\RekapGuruPdfExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index()
    {
        $guru = auth()->user()->guru;
        $hari = Carbon::now()->locale('id')->dayName;
        $tanggal = Carbon::now()->toDateString();

        // Cek hari libur
        if (HariLibur::isHariLibur($tanggal)) {
            return redirect()->route('guru.dashboard')
                ->with('warning', 'Hari ini adalah hari libur. Tidak ada absensi.');
        }

        // Process automatic alfa for expired schedules
        $this->processAutoAlfa($guru->id, $tanggal);

        // Jadwal hari ini
        $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_ke')
            ->get();

        // Status absensi hari ini
        $absensiHariIni = AbsensiGuru::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggal)
            ->pluck('jadwal_pelajaran_id')
            ->toArray();

        return view('guru.absensi.index', compact('jadwalHariIni', 'absensiHariIni', 'tanggal'));
    }

    public function create(Request $request)
    {
        $jadwalId = $request->jadwal;
        $jadwal = JadwalPelajaran::with(['kelas', 'mataPelajaran'])->findOrFail($jadwalId);
        
        // Cek apakah sudah absen
        $existingAbsensi = AbsensiGuru::where('guru_id', auth()->user()->guru->id)
            ->where('jadwal_pelajaran_id', $jadwalId)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        if ($existingAbsensi) {
            return redirect()->route('guru.absensi.index')
                ->with('error', 'Anda sudah melakukan absensi untuk jadwal ini.');
        }

        // Generate QR Code sementara untuk ditampilkan
        $tempQrCode = Str::random(32);
        $qrCode = QrCode::size(300)->generate($tempQrCode);

        return view('guru.absensi.create', compact('jadwal', 'qrCode', 'tempQrCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
            'status' => 'required|in:hadir,tidak_hadir,izin,sakit,dinas_luar,cuti',
            'alasan' => 'nullable|string|max:1000',
            'tugas' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $absensi = AbsensiGuru::create([
                'guru_id' => auth()->user()->guru->id,
                'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
                'tanggal' => now()->toDateString(),
                'jam_absen' => $request->status === 'hadir' ? now()->format('H:i') : null,
                'status' => $request->status,
                'alasan' => $request->alasan,
                'tugas' => $request->tugas,
                'is_auto_alfa' => false
            ]);

            // Kirim notifikasi Telegram ke siswa jika guru tidak hadir
            if ($request->status !== 'hadir') {
                $this->notifikasiGuruTidakHadir($absensi);
            }

            DB::commit();

            return redirect()->route('guru.absensi.index')
                ->with('success', 'Absensi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
        ]);

        // Validasi QR Code dari siswa
        $validQr = $this->validateQrCode($request->qr_code, $request->jadwal_pelajaran_id);

        if (!$validQr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa.'
            ], 400);
        }

        // Cek apakah sudah absen
        $existingAbsensi = AbsensiGuru::where('guru_id', auth()->user()->guru->id)
            ->where('jadwal_pelajaran_id', $request->jadwal_pelajaran_id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        if ($existingAbsensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi untuk jadwal ini.'
            ], 400);
        }

        // Simpan absensi
        $absensi = AbsensiGuru::create([
            'guru_id' => auth()->user()->guru->id,
            'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
            'tanggal' => now()->toDateString(),
            'jam_absen' => now()->format('H:i'),
            'status' => 'hadir',
            'is_auto_alfa' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil!',
            'data' => $absensi
        ]);
    }

    private function validateQrCode($qrCode, $jadwalId)
    {
        // Implementasi validasi QR Code
        // QR Code format: {jadwal_id}_{tanggal}_{random_string}
        $parts = explode('_', $qrCode);
        
        if (count($parts) !== 3) {
            return false;
        }

        [$qrJadwalId, $qrTanggal, $random] = $parts;

        // Validasi jadwal ID
        if ($qrJadwalId != $jadwalId) {
            return false;
        }

        // Validasi tanggal (hanya berlaku hari ini)
        if ($qrTanggal !== now()->format('Y-m-d')) {
            return false;
        }

        return true;
    }

        // PERBAIKAN untuk method processAutoAlfa di AbsensiController.php

    private function processAutoAlfa($guruId, $tanggal)
    {
        $currentTime = Carbon::now();
        $hari = $currentTime->locale('id')->dayName;

        // Ambil jadwal yang sudah lewat dan belum diabsen
        $jadwalLewat = JadwalPelajaran::where('guru_id', $guruId)
            ->where('hari', $hari)
            ->whereNotExists(function ($query) use ($tanggal) {
                $query->select(DB::raw(1))
                    ->from('absensi_guru')
                    ->whereColumn('jadwal_pelajaran.id', 'absensi_guru.jadwal_pelajaran_id')
                    ->whereDate('absensi_guru.tanggal', $tanggal);
            })
            ->get();

        foreach ($jadwalLewat as $jadwal) {
            try {
                // Validasi dan bersihkan data jam_selesai
                $jamSelesaiRaw = $jadwal->jam_selesai;
                
                // Skip jika jam_selesai null atau kosong
                if (empty($jamSelesaiRaw)) {
                    continue;
                }

                // Bersihkan whitespace
                $jamSelesaiRaw = trim($jamSelesaiRaw);
                
                // Coba berbagai format waktu yang mungkin
                $jamSelesai = null;
                
                // Format H:i (contoh: 14:30)
                if (preg_match('/^(\d{1,2}):(\d{2})$/', $jamSelesaiRaw)) {
                    $jamSelesai = Carbon::createFromFormat('H:i', $jamSelesaiRaw);
                }
                // Format H:i:s (contoh: 14:30:00)
                elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $jamSelesaiRaw)) {
                    $jamSelesai = Carbon::createFromFormat('H:i:s', $jamSelesaiRaw);
                }
                // Format datetime lengkap
                elseif (strtotime($jamSelesaiRaw)) {
                    $jamSelesai = Carbon::parse($jamSelesaiRaw);
                }
                
                // Jika gagal parsing, skip jadwal ini
                if (!$jamSelesai) {
                    \Log::warning("Gagal parsing jam_selesai untuk jadwal ID {$jadwal->id}: {$jamSelesaiRaw}");
                    continue;
                }

                // Set tanggal untuk hari ini (karena Carbon::createFromFormat hanya mengambil waktu)
                $jamSelesai->setDate($currentTime->year, $currentTime->month, $currentTime->day);
                
                // Jika sudah lewat 15 menit dari jam selesai, otomatis alfa
                if ($currentTime->isAfter($jamSelesai->addMinutes(1))) {
                    AbsensiGuru::create([
                        'guru_id' => $guruId,
                        'jadwal_pelajaran_id' => $jadwal->id,
                        'tanggal' => $tanggal,
                        'jam_absen' => null,
                        'status' => 'tidak_hadir',
                        'alasan' => 'Otomatis tidak hadir karena tidak melakukan absensi',
                        'tugas' => null,
                        'is_auto_alfa' => true
                    ]);

                    // Kirim notifikasi untuk auto alfa
                    $this->notifikasiAutoAlfa($jadwal);
                }
                
            } catch (\Exception $e) {
                // Log error tapi lanjutkan proses untuk jadwal lainnya
                \Log::error("Error processing auto alfa untuk jadwal ID {$jadwal->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    // PERBAIKAN TAMBAHAN untuk method-method lain yang mungkin bermasalah

    // Perbaikan untuk method notifikasiAutoAlfa
    private function notifikasiAutoAlfa($jadwal)
    {
        try {
            $kelas = $jadwal->kelas;
            $mapel = $jadwal->mataPelajaran;
            $guru = $jadwal->guru;

            // Validasi relasi
            if (!$kelas || !$mapel || !$guru) {
                \Log::warning("Missing relations for jadwal ID {$jadwal->id}");
                return;
            }

            $pesan = "⚠️ *PEMBERITAHUAN OTOMATIS*\n\n";
            $pesan .= "Guru: {$guru->nama_lengkap}\n";
            $pesan .= "Mata Pelajaran: {$mapel->nama_mata_pelajaran}\n";
            $pesan .= "Kelas: {$kelas->nama_kelas}\n";
            $pesan .= "Jam ke-{$jadwal->jam_ke} ({$jadwal->jam_mulai} - {$jadwal->jam_selesai})\n";
            $pesan .= "Status: Tidak Hadir (Otomatis)\n";
            $pesan .= "Keterangan: Guru tidak melakukan absensi dalam waktu yang ditentukan";

            // Kirim ke ketua kelas
            $ketuaKelas = $kelas->siswa()
                ->whereHas('user', function ($q) {
                    $q->whereNotNull('telegram_chat_id');
                })
                ->where('is_ketua_kelas', true)
                ->first();

            if ($ketuaKelas && $ketuaKelas->user && $ketuaKelas->user->telegram_chat_id) {
                $this->telegramService->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
            }
            
        } catch (\Exception $e) {
            \Log::error("Error sending auto alfa notification: " . $e->getMessage());
        }
    }

    // Perbaikan untuk method notifikasiGuruTidakHadir
    private function notifikasiGuruTidakHadir($absensi)
    {
        try {
            $jadwal = $absensi->jadwalPelajaran;
            
            // Validasi relasi
            if (!$jadwal) {
                \Log::warning("Missing jadwal relation for absensi ID {$absensi->id}");
                return;
            }
            
            $kelas = $jadwal->kelas;
            $mapel = $jadwal->mataPelajaran;
            $guru = $absensi->guru;

            // Validasi relasi lainnya
            if (!$kelas || !$mapel || !$guru) {
                \Log::warning("Missing relations for absensi ID {$absensi->id}");
                return;
            }

            $statusLabels = [
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'dinas_luar' => 'Dinas Luar',
                'cuti' => 'Cuti',
                'tidak_hadir' => 'Tidak Hadir'
            ];

            $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
            $pesan .= "Guru: {$guru->nama_lengkap}\n";
            $pesan .= "Mata Pelajaran: {$mapel->nama_mata_pelajaran}\n";
            $pesan .= "Kelas: {$kelas->nama_kelas}\n";
            $pesan .= "Jam ke-{$jadwal->jam_ke} ({$jadwal->jam_mulai} - {$jadwal->jam_selesai})\n";
            $pesan .= "Status: " . ($statusLabels[$absensi->status] ?? ucfirst($absensi->status)) . "\n";
            
            if ($absensi->alasan) {
                $pesan .= "Alasan: {$absensi->alasan}\n";
            }
            
            if ($absensi->tugas) {
                $pesan .= "\n📝 *TUGAS:*\n{$absensi->tugas}";
            }

            // Kirim ke ketua kelas
            $ketuaKelas = $kelas->siswa()
                ->whereHas('user', function ($q) {
                    $q->whereNotNull('telegram_chat_id');
                })
                ->where('is_ketua_kelas', true)
                ->first();

            if ($ketuaKelas && $ketuaKelas->user && $ketuaKelas->user->telegram_chat_id) {
                $this->telegramService->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
            }
            
        } catch (\Exception $e) {
            \Log::error("Error sending teacher absence notification: " . $e->getMessage());
        }
    }

    // TAMBAHAN: Method helper untuk validasi format waktu
    private function parseTimeFormat($timeString)
    {
        if (empty($timeString)) {
            return null;
        }

        $timeString = trim($timeString);
        
        try {
            // Format H:i (contoh: 14:30)
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $timeString)) {
                return Carbon::createFromFormat('H:i', $timeString);
            }
            // Format H:i:s (contoh: 14:30:00)
            elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeString)) {
                return Carbon::createFromFormat('H:i:s', $timeString);
            }
            // Format datetime lengkap
            elseif (strtotime($timeString)) {
                return Carbon::parse($timeString);
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::warning("Failed to parse time format: {$timeString} - " . $e->getMessage());
            return null;
        }
    }


public function rekap(Request $request)
{
    $guru = auth()->user()->guru;
    
    // Default values
    $tanggalMulai = $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d');
    $tanggalSelesai = $request->tanggal_selesai ?? now()->format('Y-m-d');
    $kelasId = $request->kelas_id ?? '';
    $mataPelajaranId = $request->mata_pelajaran_id ?? '';
    
    // Get filter options
    $kelasOptions = \App\Models\Kelas::whereHas('jadwalPelajaran', function($q) use ($guru) {
        $q->where('guru_id', $guru->id);
    })->orderBy('nama_kelas')->get();
    
    $mataPelajaranOptions = \App\Models\MataPelajaran::whereHas('jadwalPelajaran', function($q) use ($guru) {
        $q->where('guru_id', $guru->id);
    })->orderBy('nama_mata_pelajaran')->get();
    
    // Query builder
    $query = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
        ->where('guru_id', $guru->id);
    
    // Apply date filter
    $query->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
    
    // Apply class filter
    if (!empty($kelasId)) {
        $query->whereHas('jadwalPelajaran', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        });
    }
    
    // Apply subject filter
    if (!empty($mataPelajaranId)) {
        $query->whereHas('jadwalPelajaran', function($q) use ($mataPelajaranId) {
            $q->where('mata_pelajaran_id', $mataPelajaranId);
        });
    }
    
    $absensi = $query->orderBy('tanggal', 'desc')->get();
    
    $statistik = [
        'hadir' => $absensi->where('status', 'hadir')->count(),
        'izin' => $absensi->where('status', 'izin')->count(),
        'sakit' => $absensi->where('status', 'sakit')->count(),
        'dinas_luar' => $absensi->where('status', 'dinas_luar')->count(),
        'cuti' => $absensi->where('status', 'cuti')->count(),
        'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
    ];
    
    // Info untuk display
    $periodeInfo = Carbon::parse($tanggalMulai)->format('d/m/Y') . ' - ' . Carbon::parse($tanggalSelesai)->format('d/m/Y');
    
    $kelasInfo = null;
    if (!empty($kelasId)) {
        $kelas = \App\Models\Kelas::find($kelasId);
        $kelasInfo = $kelas ? $kelas->nama_kelas : null;
    }
    
    $mataPelajaranInfo = null;
    if (!empty($mataPelajaranId)) {
        $mataPelajaran = \App\Models\MataPelajaran::find($mataPelajaranId);
        $mataPelajaranInfo = $mataPelajaran ? $mataPelajaran->nama_mata_pelajaran : null;
    }
    
    return view('guru.absensi.rekap', compact(
        'absensi', 'statistik', 'tanggalMulai', 'tanggalSelesai', 
        'kelasId', 'mataPelajaranId', 'kelasOptions', 'mataPelajaranOptions',
        'periodeInfo', 'kelasInfo', 'mataPelajaranInfo'
    ));
}

// Export Excel dengan filter baru
public function exportRekap(Request $request)
{
    $guru = auth()->user()->guru;
    $filterParams = $this->getFilterParams($request);
    
    return Excel::download(
        new RekapGuruExport($guru->id, $filterParams),
        $this->generateFileName($guru, $filterParams, 'xlsx')
    );
}

// Export PDF dengan filter baru
public function exportRekapPdf(Request $request)
{
    $guru = auth()->user()->guru;
    $filterParams = $this->getFilterParams($request);
    
    try {
        $pdfExport = new RekapGuruPdfExport($guru->id, $filterParams);
        $data = $pdfExport->getData();
        
        $pdf = PDF::loadView('guru.absensi.rekap-pdf', $data);
        
        // Set paper dan orientasi
        $pdf->setPaper('A4', 'landscape');
        
        // Set opsi PDF
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        $fileName = $this->generateFileName($guru, $filterParams, 'pdf');
        
        return $pdf->download($fileName);
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan saat menggenerate PDF: ' . $e->getMessage());
    }
}

// Helper method untuk mendapatkan parameter filter
private function getFilterParams($request)
{
    return [
        'tanggal_mulai' => $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d'),
        'tanggal_selesai' => $request->tanggal_selesai ?? now()->format('Y-m-d'),
        'kelas_id' => $request->kelas_id ?? '',
        'mata_pelajaran_id' => $request->mata_pelajaran_id ?? '',
    ];
}

// Helper method untuk generate nama file
private function generateFileName($guru, $filterParams, $extension)
{
    $namaGuru = str_replace(' ', '_', $guru->nama_lengkap);
    $tanggalMulai = str_replace('-', '', $filterParams['tanggal_mulai']);
    $tanggalSelesai = str_replace('-', '', $filterParams['tanggal_selesai']);
    
    $fileName = "rekap_absensi_{$namaGuru}_{$tanggalMulai}_{$tanggalSelesai}";
    
    if (!empty($filterParams['kelas_id'])) {
        $kelas = \App\Models\Kelas::find($filterParams['kelas_id']);
        if ($kelas) {
            $fileName .= '_' . str_replace(' ', '_', $kelas->nama_kelas);
        }
    }
    
    if (!empty($filterParams['mata_pelajaran_id'])) {
        $mataPelajaran = \App\Models\MataPelajaran::find($filterParams['mata_pelajaran_id']);
        if ($mataPelajaran) {
            $fileName .= '_' . str_replace(' ', '_', $mataPelajaran->nama_mata_pelajaran);
        }
    }
    
    return $fileName . ".{$extension}";
}


    // public function rekap(Request $request)
    // {
    //     $guru = auth()->user()->guru;
    //     $bulan = $request->bulan ?? now()->month;
    //     $tahun = $request->tahun ?? now()->year;

    //     $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
    //         ->where('guru_id', $guru->id)
    //         ->whereMonth('tanggal', $bulan)
    //         ->whereYear('tanggal', $tahun)
    //         ->orderBy('tanggal', 'desc')
    //         ->get();

    //     $statistik = [
    //         'hadir' => $absensi->where('status', 'hadir')->count(),
    //         'izin' => $absensi->where('status', 'izin')->count(),
    //         'sakit' => $absensi->where('status', 'sakit')->count(),
    //         'dinas_luar' => $absensi->where('status', 'dinas_luar')->count(),
    //         'cuti' => $absensi->where('status', 'cuti')->count(),
    //         'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
    //     ];

    //     return view('guru.absensi.rekap', compact('absensi', 'statistik', 'bulan', 'tahun'));
    // }

    // // Export Excel
    // public function exportRekap(Request $request)
    // {
    //     $guru = auth()->user()->guru;
    //     $bulan = $request->bulan ?? now()->month;
    //     $tahun = $request->tahun ?? now()->year;
        
    //     return Excel::download(
    //         new RekapGuruExport($guru->id, $bulan, $tahun),
    //         "rekap_absensi_{$guru->nama_lengkap}_{$bulan}_{$tahun}.xlsx"
    //     );
    // }

    // // Export PDF
    // public function exportRekapPdf(Request $request)
    // {
    //     $guru = auth()->user()->guru;
    //     $bulan = $request->bulan ?? now()->month;
    //     $tahun = $request->tahun ?? now()->year;
        
    //     try {
    //         $pdfExport = new RekapGuruPdfExport($guru->id, $bulan, $tahun);
    //         $data = $pdfExport->getData();
            
    //         $pdf = PDF::loadView('guru.absensi.rekap-pdf', $data);
            
    //         // Set paper dan orientasi
    //         $pdf->setPaper('A4', 'landscape');
            
    //         // Set opsi PDF
    //         $pdf->setOptions([
    //             'isHtml5ParserEnabled' => true,
    //             'isRemoteEnabled' => true,
    //             'defaultFont' => 'Arial'
    //         ]);
            
    //         $fileName = "rekap_absensi_{$guru->nama_lengkap}_{$bulan}_{$tahun}.pdf";
            
    //         return $pdf->download($fileName);
            
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Terjadi kesalahan saat menggenerate PDF: ' . $e->getMessage());
    //     }
    // }
}





// namespace App\Http\Controllers\Guru;

// use App\Http\Controllers\Controller;
// use App\Models\AbsensiGuru;
// use App\Models\JadwalPelajaran;
// use App\Models\HariLibur;
// use App\Services\TelegramService;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;
// use App\Exports\RekapGuruExport;
// use App\Exports\RekapGuruPdfExport;
// use Maatwebsite\Excel\Facades\Excel;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Illuminate\Support\Str;

// class AbsensiController extends Controller
// {
//     protected $telegramService;

//     public function __construct(TelegramService $telegramService)
//     {
//         $this->telegramService = $telegramService;
//     }

//     public function index()
//     {
//         $guru = auth()->user()->guru;
//         $hari = Carbon::now()->locale('id')->dayName;
//         $tanggal = Carbon::now()->toDateString();

//         // Cek hari libur
//         if (HariLibur::isHariLibur($tanggal)) {
//             return redirect()->route('guru.dashboard')
//                 ->with('warning', 'Hari ini adalah hari libur. Tidak ada absensi.');
//         }

//         // Jadwal hari ini
//         $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
//             ->where('guru_id', $guru->id)
//             ->where('hari', $hari)
//             ->orderBy('jam_ke')
//             ->get();

//         // Status absensi hari ini
//         $absensiHariIni = AbsensiGuru::where('guru_id', $guru->id)
//             ->whereDate('tanggal', $tanggal)
//             ->pluck('jadwal_pelajaran_id')
//             ->toArray();

//         return view('guru.absensi.index', compact('jadwalHariIni', 'absensiHariIni', 'tanggal'));
//     }

//     public function create(Request $request)
//     {
//         $jadwalId = $request->jadwal;
//         $jadwal = JadwalPelajaran::with(['kelas', 'mataPelajaran'])->findOrFail($jadwalId);
        
//         // Cek apakah sudah absen
//         $existingAbsensi = AbsensiGuru::where('guru_id', auth()->user()->guru->id)
//             ->where('jadwal_pelajaran_id', $jadwalId)
//             ->whereDate('tanggal', now()->toDateString())
//             ->first();

//         if ($existingAbsensi) {
//             return redirect()->route('guru.absensi.index')
//                 ->with('error', 'Anda sudah melakukan absensi untuk jadwal ini.');
//         }

//         // Generate QR Code sementara untuk ditampilkan
//         $tempQrCode = Str::random(32);
//         $qrCode = QrCode::size(300)->generate($tempQrCode);

//         return view('guru.absensi.create', compact('jadwal', 'qrCode', 'tempQrCode'));
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
//             'status' => 'required|in:hadir,tidak_hadir,izin,sakit',
//             'alasan' => 'required_unless:status,hadir',
//             'tugas' => 'required_if:status,tidak_hadir',
//         ]);

//         DB::beginTransaction();
//         try {
//             $absensi = AbsensiGuru::create([
//                 'guru_id' => auth()->user()->guru->id,
//                 'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
//                 'tanggal' => now()->toDateString(),
//                 'jam_absen' => $request->status === 'hadir' ? now()->format('H:i') : null,
//                 'status' => $request->status,
//                 'alasan' => $request->alasan,
//                 'tugas' => $request->tugas,
//             ]);

//             // Kirim notifikasi Telegram ke siswa jika guru tidak hadir
//             if ($request->status !== 'hadir') {
//                 $this->notifikasiGuruTidakHadir($absensi);
//             }

//             DB::commit();

//             return redirect()->route('guru.absensi.index')
//                 ->with('success', 'Absensi berhasil disimpan.');

//         } catch (\Exception $e) {
//             DB::rollback();
//             return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function scanQr(Request $request)
//     {
//         $request->validate([
//             'qr_code' => 'required|string',
//             'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
//         ]);

//         // Validasi QR Code dari siswa
//         $validQr = $this->validateQrCode($request->qr_code, $request->jadwal_pelajaran_id);

//         if (!$validQr) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'QR Code tidak valid atau sudah kadaluarsa.'
//             ], 400);
//         }

//         // Simpan absensi
//         $absensi = AbsensiGuru::create([
//             'guru_id' => auth()->user()->guru->id,
//             'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
//             'tanggal' => now()->toDateString(),
//             'jam_absen' => now()->format('H:i'),
//             'status' => 'hadir',
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Absensi berhasil!',
//             'data' => $absensi
//         ]);
//     }

//     private function validateQrCode($qrCode, $jadwalId)
//     {
//         // Implementasi validasi QR Code
//         // QR Code format: {jadwal_id}_{tanggal}_{random_string}
//         $parts = explode('_', $qrCode);
        
//         if (count($parts) !== 3) {
//             return false;
//         }

//         [$qrJadwalId, $qrTanggal, $random] = $parts;

//         // Validasi jadwal ID
//         if ($qrJadwalId != $jadwalId) {
//             return false;
//         }

//         // Validasi tanggal (hanya berlaku hari ini)
//         if ($qrTanggal !== now()->format('Y-m-d')) {
//             return false;
//         }

//         // QR Code valid selama 15 menit (bisa disimpan di cache)
//         // Implementasi cache untuk validasi waktu bisa ditambahkan

//         return true;
//     }

//     private function notifikasiGuruTidakHadir($absensi)
//     {
//         $jadwal = $absensi->jadwalPelajaran;
//         $kelas = $jadwal->kelas;
//         $mapel = $jadwal->mataPelajaran;
//         $guru = $absensi->guru;

//         $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
//         $pesan .= "Guru: {$guru->nama_lengkap}\n";
//         $pesan .= "Mata Pelajaran: {$mapel->nama_mata_pelajaran}\n";
//         $pesan .= "Kelas: {$kelas->nama_kelas}\n";
//         $pesan .= "Jam ke-{$jadwal->jam_ke} ({$jadwal->jam_mulai} - {$jadwal->jam_selesai})\n";
//         $pesan .= "Status: " . ucfirst($absensi->status) . "\n";
        
//         if ($absensi->alasan) {
//             $pesan .= "Alasan: {$absensi->alasan}\n";
//         }
        
//         if ($absensi->tugas) {
//             $pesan .= "\n📝 *TUGAS:*\n{$absensi->tugas}";
//         }

//         // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();

//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             $this->telegramService->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
//     }

//     public function rekap(Request $request)
//     {
//         $guru = auth()->user()->guru;
//         $bulan = $request->bulan ?? now()->month;
//         $tahun = $request->tahun ?? now()->year;

//         $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
//             ->where('guru_id', $guru->id)
//             ->whereMonth('tanggal', $bulan)
//             ->whereYear('tanggal', $tahun)
//             ->orderBy('tanggal', 'desc')
//             ->get();

//         $statistik = [
//             'hadir' => $absensi->where('status', 'hadir')->count(),
//             'izin' => $absensi->where('status', 'izin')->count(),
//             'sakit' => $absensi->where('status', 'sakit')->count(),
//             'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
//         ];

//         return view('guru.absensi.rekap', compact('absensi', 'statistik', 'bulan', 'tahun'));
//     }

//     // Export Excel
//     public function exportRekap(Request $request)
//     {
//         $guru = auth()->user()->guru;
//         $bulan = $request->bulan ?? now()->month;
//         $tahun = $request->tahun ?? now()->year;
        
//         return Excel::download(
//             new RekapGuruExport($guru->id, $bulan, $tahun),
//             "rekap_absensi_{$guru->nama_lengkap}_{$bulan}_{$tahun}.xlsx"
//         );
//     }

//     // Export PDF - Method baru
//     public function exportRekapPdf(Request $request)
//     {
//         $guru = auth()->user()->guru;
//         $bulan = $request->bulan ?? now()->month;
//         $tahun = $request->tahun ?? now()->year;
        
//         try {
//             $pdfExport = new RekapGuruPdfExport($guru->id, $bulan, $tahun);
//             $data = $pdfExport->getData();
            
//             $pdf = PDF::loadView('guru.absensi.rekap-pdf', $data);
            
//             // Set paper dan orientasi
//             $pdf->setPaper('A4', 'landscape');
            
//             // Set opsi PDF
//             $pdf->setOptions([
//                 'isHtml5ParserEnabled' => true,
//                 'isRemoteEnabled' => true,
//                 'defaultFont' => 'Arial'
//             ]);
            
//             $fileName = "rekap_absensi_{$guru->nama_lengkap}_{$bulan}_{$tahun}.pdf";
            
//             return $pdf->download($fileName);
            
//         } catch (\Exception $e) {
//             return redirect()->back()->with('error', 'Terjadi kesalahan saat menggenerate PDF: ' . $e->getMessage());
//         }
//     }
// }










// namespace App\Http\Controllers\Guru;

// use App\Http\Controllers\Controller;
// use App\Models\AbsensiGuru;
// use App\Models\JadwalPelajaran;
// use App\Models\HariLibur;
// use App\Services\TelegramService;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;
// use App\Exports\RekapGuruExport;
// use Maatwebsite\Excel\Facades\Excel;
// use Illuminate\Support\Str;

// class AbsensiController extends Controller
// {
//     protected $telegramService;

//     public function __construct(TelegramService $telegramService)
//     {
//         $this->telegramService = $telegramService;
//     }

//     public function index()
//     {
//         $guru = auth()->user()->guru;
//         $hari = Carbon::now()->locale('id')->dayName;
//         $tanggal = Carbon::now()->toDateString();

//         // Cek hari libur
//         if (HariLibur::isHariLibur($tanggal)) {
//             return redirect()->route('guru.dashboard')
//                 ->with('warning', 'Hari ini adalah hari libur. Tidak ada absensi.');
//         }

//         // Jadwal hari ini
//         $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
//             ->where('guru_id', $guru->id)
//             ->where('hari', $hari)
//             ->orderBy('jam_ke')
//             ->get();

//         // Status absensi hari ini
//         $absensiHariIni = AbsensiGuru::where('guru_id', $guru->id)
//             ->whereDate('tanggal', $tanggal)
//             ->pluck('jadwal_pelajaran_id')
//             ->toArray();

//         return view('guru.absensi.index', compact('jadwalHariIni', 'absensiHariIni', 'tanggal'));
//     }

//     public function create(Request $request)
//     {
//         $jadwalId = $request->jadwal;
//         $jadwal = JadwalPelajaran::with(['kelas', 'mataPelajaran'])->findOrFail($jadwalId);
        
//         // Cek apakah sudah absen
//         $existingAbsensi = AbsensiGuru::where('guru_id', auth()->user()->guru->id)
//             ->where('jadwal_pelajaran_id', $jadwalId)
//             ->whereDate('tanggal', now()->toDateString())
//             ->first();

//         if ($existingAbsensi) {
//             return redirect()->route('guru.absensi.index')
//                 ->with('error', 'Anda sudah melakukan absensi untuk jadwal ini.');
//         }

//         // Generate QR Code sementara untuk ditampilkan
//         $tempQrCode = Str::random(32);
//         $qrCode = QrCode::size(300)->generate($tempQrCode);

//         return view('guru.absensi.create', compact('jadwal', 'qrCode', 'tempQrCode'));
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
//             'status' => 'required|in:hadir,tidak_hadir,izin,sakit',
//             'alasan' => 'required_unless:status,hadir',
//             'tugas' => 'required_if:status,tidak_hadir',
//         ]);

//         DB::beginTransaction();
//         try {
//             $absensi = AbsensiGuru::create([
//                 'guru_id' => auth()->user()->guru->id,
//                 'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
//                 'tanggal' => now()->toDateString(),
//                 'jam_absen' => $request->status === 'hadir' ? now()->format('H:i') : null,
//                 'status' => $request->status,
//                 'alasan' => $request->alasan,
//                 'tugas' => $request->tugas,
//             ]);

//             // Kirim notifikasi Telegram ke siswa jika guru tidak hadir
//             if ($request->status !== 'hadir') {
//                 $this->notifikasiGuruTidakHadir($absensi);
//             }

//             DB::commit();

//             return redirect()->route('guru.absensi.index')
//                 ->with('success', 'Absensi berhasil disimpan.');

//         } catch (\Exception $e) {
//             DB::rollback();
//             return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function scanQr(Request $request)
//     {
//         $request->validate([
//             'qr_code' => 'required|string',
//             'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
//         ]);

//         // Validasi QR Code dari siswa
//         $validQr = $this->validateQrCode($request->qr_code, $request->jadwal_pelajaran_id);

//         if (!$validQr) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'QR Code tidak valid atau sudah kadaluarsa.'
//             ], 400);
//         }

//         // Simpan absensi
//         $absensi = AbsensiGuru::create([
//             'guru_id' => auth()->user()->guru->id,
//             'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
//             'tanggal' => now()->toDateString(),
//             'jam_absen' => now()->format('H:i'),
//             'status' => 'hadir',
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Absensi berhasil!',
//             'data' => $absensi
//         ]);
//     }

//     private function validateQrCode($qrCode, $jadwalId)
//     {
//         // Implementasi validasi QR Code
//         // QR Code format: {jadwal_id}_{tanggal}_{random_string}
//         $parts = explode('_', $qrCode);
        
//         if (count($parts) !== 3) {
//             return false;
//         }

//         [$qrJadwalId, $qrTanggal, $random] = $parts;

//         // Validasi jadwal ID
//         if ($qrJadwalId != $jadwalId) {
//             return false;
//         }

//         // Validasi tanggal (hanya berlaku hari ini)
//         if ($qrTanggal !== now()->format('Y-m-d')) {
//             return false;
//         }

//         // QR Code valid selama 15 menit (bisa disimpan di cache)
//         // Implementasi cache untuk validasi waktu bisa ditambahkan

//         return true;
//     }

//     private function notifikasiGuruTidakHadir($absensi)
//     {
//         $jadwal = $absensi->jadwalPelajaran;
//         $kelas = $jadwal->kelas;
//         $mapel = $jadwal->mataPelajaran;
//         $guru = $absensi->guru;

//         $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
//         $pesan .= "Guru: {$guru->nama_lengkap}\n";
//         $pesan .= "Mata Pelajaran: {$mapel->nama_mata_pelajaran}\n";
//         $pesan .= "Kelas: {$kelas->nama_kelas}\n";
//         $pesan .= "Jam ke-{$jadwal->jam_ke} ({$jadwal->jam_mulai} - {$jadwal->jam_selesai})\n";
//         $pesan .= "Status: " . ucfirst($absensi->status) . "\n";
        
//         if ($absensi->alasan) {
//             $pesan .= "Alasan: {$absensi->alasan}\n";
//         }
        
//         if ($absensi->tugas) {
//             $pesan .= "\n📝 *TUGAS:*\n{$absensi->tugas}";
//         }

//         // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();

//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             $this->telegramService->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
//     }

//     public function rekap(Request $request)
//     {
//         $guru = auth()->user()->guru;
//         $bulan = $request->bulan ?? now()->month;
//         $tahun = $request->tahun ?? now()->year;

//         $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
//             ->where('guru_id', $guru->id)
//             ->whereMonth('tanggal', $bulan)
//             ->whereYear('tanggal', $tahun)
//             ->orderBy('tanggal', 'desc')
//             ->get();

//         $statistik = [
//             'hadir' => $absensi->where('status', 'hadir')->count(),
//             'izin' => $absensi->where('status', 'izin')->count(),
//             'sakit' => $absensi->where('status', 'sakit')->count(),
//             'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
//         ];

//         return view('guru.absensi.rekap', compact('absensi', 'statistik', 'bulan', 'tahun'));
//     }


// // Tambahkan method ini di dalam class AbsensiController
// public function exportRekap(Request $request)
// {
//     $guru = auth()->user()->guru;
//     $bulan = $request->bulan ?? now()->month;
//     $tahun = $request->tahun ?? now()->year;
    
//     return Excel::download(
//         new RekapGuruExport($guru->id, $bulan, $tahun),
//         "rekap_absensi_{$guru->nama_lengkap}_{$bulan}_{$tahun}.xlsx"
//     );
// }

//  }





// app/Http/Controllers/Guru/AbsensiController.php

// namespace App\Http\Controllers\Guru;

// use App\Http\Controllers\Controller;
// use App\Models\AbsensiGuru;
// use App\Models\JadwalPelajaran;
// use App\Models\HariLibur;
// use App\Services\TelegramService;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class AbsensiController extends Controller
// {
//     protected $telegramService;

//     public function __construct(TelegramService $telegramService)
//     {
//         $this->telegramService = $telegramService;
//     }

//     public function index()
//     {
//         $guru = auth()->user()->guru;
//         $hari = Carbon::now()->locale('id')->dayName;
//         $tanggal = Carbon::now()->toDateString();

        // Cek hari libur
        // if (HariLibur::where('tanggal', $tanggal)->exists()) {
        //     return redirect()->route('guru.dashboard')
        //         ->with('warning', 'Hari ini adalah hari libur. Tidak ada absensi.');
        // }

        // Jadwal hari ini
        // $jadwalHariIni = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
        //     ->where('guru_id', $guru->id)
        //     ->where('hari', $hari)
        //     ->orderBy('jam_ke')
        //     ->get();

        // Status absensi hari ini
    //     $absensiHariIni = AbsensiGuru::where('guru_id', $guru->id)
    //         ->whereDate('tanggal', $tanggal)
    //         ->pluck('jadwal_pelajaran_id')
    //         ->toArray();

    //     return view('guru.absensi.index', compact('jadwalHariIni', 'absensiHariIni', 'tanggal'));
    // }

    // public function create(Request $request)
    // {
    //     $jadwalId = $request->jadwal;
    //     $jadwal = JadwalPelajaran::with(['kelas', 'mataPelajaran'])->findOrFail($jadwalId);
        
        // Cek apakah sudah absen
    //     $existingAbsensi = AbsensiGuru::where('guru_id', auth()->user()->guru->id)
    //         ->where('jadwal_pelajaran_id', $jadwalId)
    //         ->whereDate('tanggal', now()->toDateString())
    //         ->first();

    //     if ($existingAbsensi) {
    //         return redirect()->route('guru.absensi.index')
    //             ->with('error', 'Anda sudah melakukan absensi untuk jadwal ini.');
    //     }

    //     return view('guru.absensi.create', compact('jadwal'));
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
    //         'status' => 'required|in:hadir,tidak_hadir,izin,sakit',
    //         'alasan' => 'required_unless:status,hadir',
    //         'tugas' => 'required_if:status,tidak_hadir',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $absensi = AbsensiGuru::create([
    //             'guru_id' => auth()->user()->guru->id,
    //             'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
    //             'tanggal' => now()->toDateString(),
    //             'jam_absen' => $request->status === 'hadir' ? now()->format('H:i') : null,
    //             'status' => $request->status,
    //             'alasan' => $request->alasan,
    //             'tugas' => $request->tugas,
    //         ]);

    //         // Kirim notifikasi Telegram ke siswa jika guru tidak hadir
    //         if ($request->status !== 'hadir' && $this->telegramService) {
    //             $this->notifikasiGuruTidakHadir($absensi);
    //         }

    //         DB::commit();

    //         return redirect()->route('guru.absensi.index')
    //             ->with('success', 'Absensi berhasil disimpan.');

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    // public function scanQr(Request $request)
    // {
    //     $request->validate([
    //         'qr_code' => 'required|string',
    //         'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
    //     ]);

        // Validasi QR Code dari siswa
        // Implementasi QR validation disini

        // Simpan absensi
    //     $absensi = AbsensiGuru::create([
    //         'guru_id' => auth()->user()->guru->id,
    //         'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
    //         'tanggal' => now()->toDateString(),
    //         'jam_absen' => now()->format('H:i'),
    //         'status' => 'hadir',
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Absensi berhasil!',
    //         'data' => $absensi
    //     ]);
    // }

    // private function notifikasiGuruTidakHadir($absensi)
    // {
    //     $jadwal = $absensi->jadwalPelajaran;
    //     $kelas = $jadwal->kelas;
    //     $mapel = $jadwal->mataPelajaran;
    //     $guru = $absensi->guru;

    //     $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
    //     $pesan .= "Guru: {$guru->nama_lengkap}\n";
    //     $pesan .= "Mata Pelajaran: {$mapel->nama_mata_pelajaran}\n";
    //     $pesan .= "Kelas: {$kelas->nama_kelas}\n";
    //     $pesan .= "Jam ke-{$jadwal->jam_ke} ({$jadwal->jam_mulai} - {$jadwal->jam_selesai})\n";
    //     $pesan .= "Status: " . ucfirst($absensi->status) . "\n";
        
    //     if ($absensi->alasan) {
    //         $pesan .= "Alasan: {$absensi->alasan}\n";
    //     }
        
    //     if ($absensi->tugas) {
    //         $pesan .= "\n📝 *TUGAS:*\n{$absensi->tugas}";
    //     }

        // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();

//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             $this->telegramService->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
//     }

//     public function rekap(Request $request)
//     {
//         $guru = auth()->user()->guru;
//         $bulan = $request->bulan ?? now()->month;
//         $tahun = $request->tahun ?? now()->year;

//         $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
//             ->where('guru_id', $guru->id)
//             ->whereMonth('tanggal', $bulan)
//             ->whereYear('tanggal', $tahun)
//             ->orderBy('tanggal', 'desc')
//             ->get();

//         $statistik = [
//             'hadir' => $absensi->where('status', 'hadir')->count(),
//             'izin' => $absensi->where('status', 'izin')->count(),
//             'sakit' => $absensi->where('status', 'sakit')->count(),
//             'alpa' => $absensi->where('status', 'tidak_hadir')->count(),
//         ];

//         return view('guru.absensi.rekap', compact('absensi', 'statistik', 'bulan', 'tahun'));
//     }
// }