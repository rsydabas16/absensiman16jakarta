<?php
// ==================== MODEL ABSENSI SISWA LENGKAP ====================
// app/Models/AbsensiSiswa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AbsensiSiswa extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa';
    
    protected $fillable = [
        'siswa_id',
        'kelas_id', 
        'tanggal',
        'status',
        'keterangan',
        'dicatat_oleh'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    // Status yang tersedia
    const STATUS_HADIR = 'hadir';
    const STATUS_IZIN = 'izin';
    const STATUS_SAKIT = 'sakit';
    const STATUS_ALFA = 'alfa';

    // ==================== RELASI ====================
    
    // Relasi ke Siswa (yang diabsen)
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Relasi ke siswa yang mencatat (ketua/wakil kelas)
    public function pencatat()
    {
        return $this->belongsTo(Siswa::class, 'dicatat_oleh');
    }

    // ==================== SCOPE METHODS ====================
    
    // Scope untuk filter tanggal
    public function scopeFilterTanggal($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        }
        return $query;
    }

    // Scope untuk filter kelas
    public function scopeFilterKelas($query, $kelasId = null)
    {
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        return $query;
    }

    // Scope untuk filter status
    public function scopeFilterStatus($query, $status = null)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    // Scope untuk hari ini
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    // Scope untuk minggu ini
    public function scopeMingguIni($query)
    {
        return $query->whereBetween('tanggal', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    // Scope untuk bulan ini
    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', Carbon::now()->month)
                    ->whereYear('tanggal', Carbon::now()->year);
    }

    // ==================== ACCESSOR & MUTATOR ====================

    // Accessor untuk status dengan format title case
    public function getStatusFormattedAttribute()
    {
        return ucfirst($this->status);
    }

    // Accessor untuk tanggal dalam format Indonesia
    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal->locale('id')->isoFormat('dddd, D MMMM Y');
    }

    // Accessor untuk warna badge berdasarkan status
    public function getBadgeColorAttribute()
    {
        $colors = [
            self::STATUS_HADIR => 'success',
            self::STATUS_IZIN => 'info', 
            self::STATUS_SAKIT => 'warning',
            self::STATUS_ALFA => 'danger'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    // Accessor untuk icon berdasarkan status
    public function getStatusIconAttribute()
    {
        $icons = [
            self::STATUS_HADIR => 'bx-check-circle',
            self::STATUS_IZIN => 'bx-info-circle',
            self::STATUS_SAKIT => 'bx-heart',
            self::STATUS_ALFA => 'bx-x-circle'
        ];
        
        return $icons[$this->status] ?? 'bx-help-circle';
    }

    // ==================== HELPER METHODS ====================

    // Method untuk mendapatkan semua status yang tersedia
    public static function getAllStatus()
    {
        return [
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_IZIN => 'Izin',
            self::STATUS_SAKIT => 'Sakit',
            self::STATUS_ALFA => 'Alfa'
        ];
    }

    // Method untuk validasi status
    public static function isValidStatus($status)
    {
        return in_array($status, [
            self::STATUS_HADIR,
            self::STATUS_IZIN,
            self::STATUS_SAKIT,
            self::STATUS_ALFA
        ]);
    }

    // Method untuk mendapatkan statistik per kelas
    public static function getStatistikKelas($kelasId, $startDate = null, $endDate = null)
    {
        $query = self::where('kelas_id', $kelasId);
        
        if ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        }
        
        return $query->selectRaw('status, COUNT(*) as jumlah')
                    ->groupBy('status')
                    ->pluck('jumlah', 'status')
                    ->toArray();
    }

    // Method untuk mendapatkan tren kehadiran harian
    public static function getTrenHarian($kelasId = null, $days = 30)
    {
        $query = self::query();
        
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        
        return $query->whereBetween('tanggal', [
                        Carbon::now()->subDays($days),
                        Carbon::now()
                    ])
                    ->selectRaw('DATE(tanggal) as tanggal, status, COUNT(*) as jumlah')
                    ->groupBy('tanggal', 'status')
                    ->orderBy('tanggal')
                    ->get()
                    ->groupBy('tanggal');
    }

    // ==================== BOOT METHOD ====================
    
    protected static function boot()
    {
        parent::boot();
        
        // Event saat absensi dibuat
        static::creating(function ($absensi) {
            // Validasi tidak boleh duplikasi absensi per siswa per hari
            $exists = self::where('siswa_id', $absensi->siswa_id)
                         ->where('tanggal', $absensi->tanggal)
                         ->exists();
            
            if ($exists) {
                throw new \Exception('Siswa sudah memiliki data absensi untuk tanggal ini.');
            }
        });
        
        // Event saat absensi diupdate
        static::updating(function ($absensi) {
            // Validasi tidak boleh duplikasi saat update
            if ($absensi->isDirty(['siswa_id', 'tanggal'])) {
                $exists = self::where('siswa_id', $absensi->siswa_id)
                             ->where('tanggal', $absensi->tanggal)
                             ->where('id', '!=', $absensi->id)
                             ->exists();
                
                if ($exists) {
                    throw new \Exception('Siswa sudah memiliki data absensi untuk tanggal ini.');
                }
            }
        });
    }
}






// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Carbon\Carbon;

// class AbsensiSiswa extends Model
// {
//     use HasFactory;

//     protected $table = 'absensi_siswa';
    
//     protected $fillable = [
//         'siswa_id',
//         'kelas_id', 
//         'tanggal',
//         'status',
//         'keterangan',
//         'dicatat_oleh'
//     ];

//     protected $casts = [
//         'tanggal' => 'date'
//     ];

//     // Relasi ke Siswa
//     public function siswa()
//     {
//         return $this->belongsTo(Siswa::class);
//     }

//     // Relasi ke Kelas
//     public function kelas()
//     {
//         return $this->belongsTo(Kelas::class);
//     }

//     // Relasi ke siswa yang mencatat (ketua/wakil kelas)
//     public function pencatat()
//     {
//         return $this->belongsTo(Siswa::class, 'dicatat_oleh');
//     }

//     // Scope untuk filter tanggal
//     public function scopeFilterTanggal($query, $startDate = null, $endDate = null)
//     {
//         if ($startDate) {
//             $query->where('tanggal', '>=', $startDate);
//         }
//         if ($endDate) {
//             $query->where('tanggal', '<=', $endDate);
//         }
//         return $query;
//     }

//     // Scope untuk filter kelas
//     public function scopeFilterKelas($query, $kelasId = null)
//     {
//         if ($kelasId) {
//             $query->where('kelas_id', $kelasId);
//         }
//         return $query;
//     }
// }