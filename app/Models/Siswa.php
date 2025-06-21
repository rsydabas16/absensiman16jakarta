<?php
// app/Models/Siswa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    
    protected $fillable = [
        'user_id',
        'nisn',
        'nama_lengkap',
        'kelas_id',
        'jenis_kelamin',
        'no_hp',
        'alamat',
        'is_ketua_kelas',
        'is_wakil_ketua'
    ];

    protected $casts = [
        'is_ketua_kelas' => 'boolean',
        'is_wakil_ketua' => 'boolean'
    ];

    // ==================== RELASI UTAMA ====================
    
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi ke Materi Pembelajaran (yang sudah ada)
    public function materiPembelajaran()
    {
        return $this->hasMany(MateriPembelajaran::class);
    }

    // ==================== RELASI ABSENSI SISWA (BARU) ====================
    
    // Relasi ke Absensi Siswa (sebagai siswa yang diabsen)
    public function absensiSiswa()
    {
        return $this->hasMany(AbsensiSiswa::class, 'siswa_id');
    }

    // Relasi ke Absensi yang dicatat (sebagai ketua/wakil yang mencatat)
    public function absensiYangDicatat()
    {
        return $this->hasMany(AbsensiSiswa::class, 'dicatat_oleh');
    }

    // ==================== HELPER METHODS ====================
    
    // Helper method untuk cek apakah ketua atau wakil kelas
    public function isKetuaAtauWakil()
    {
        return $this->is_ketua_kelas || $this->is_wakil_ketua;
    }

    // Helper method untuk mendapatkan role dalam kelas
    public function getRoleKelas()
    {
        if ($this->is_ketua_kelas) {
            return 'Ketua Kelas';
        } elseif ($this->is_wakil_ketua) {
            return 'Wakil Ketua Kelas';
        }
        return 'Siswa';
    }

    // Helper method untuk mendapatkan nama lengkap dengan NISN
    public function getNamaLengkapDenganNisn()
    {
        return $this->nama_lengkap . ' (' . $this->nisn . ')';
    }

    // ==================== SCOPE METHODS ====================
    
    // Scope untuk filter berdasarkan kelas
    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    // Scope untuk mendapatkan hanya ketua dan wakil kelas
    public function scopeKetuaDanWakil($query)
    {
        return $query->where(function($q) {
            $q->where('is_ketua_kelas', true)
              ->orWhere('is_wakil_ketua', true);
        });
    }

    // Scope untuk mendapatkan siswa berdasarkan jenis kelamin
    public function scopeByJenisKelamin($query, $jenisKelamin)
    {
        return $query->where('jenis_kelamin', $jenisKelamin);
    }

    // ==================== STATISTIK ABSENSI METHODS ====================
    
    // Method untuk mendapatkan statistik absensi siswa dalam periode tertentu
    public function getStatistikAbsensi($startDate = null, $endDate = null)
    {
        $query = $this->absensiSiswa();
        
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

    // Method untuk mendapatkan persentase kehadiran
    public function getPersentaseKehadiran($startDate = null, $endDate = null)
    {
        $statistik = $this->getStatistikAbsensi($startDate, $endDate);
        $totalHadir = $statistik['hadir'] ?? 0;
        $totalAll = array_sum($statistik);
        
        if ($totalAll == 0) {
            return 0;
        }
        
        return round(($totalHadir / $totalAll) * 100, 2);
    }

    // Method untuk mendapatkan absensi hari ini
    public function getAbsensiHariIni()
    {
        return $this->absensiSiswa()
                   ->whereDate('tanggal', today())
                   ->first();
    }

    // Method untuk cek apakah sudah absen hari ini
    public function sudahAbsenHariIni()
    {
        return $this->absensiSiswa()
                   ->whereDate('tanggal', today())
                   ->exists();
    }

    // ==================== ACCESSOR & MUTATOR ====================
    
    // Accessor untuk nama lengkap dengan capitalize
    public function getNamaLengkapCapitalizedAttribute()
    {
        return ucwords(strtolower($this->nama_lengkap));
    }

    // Accessor untuk format nomor HP
    public function getNoHpFormattedAttribute()
    {
        if (!$this->no_hp) {
            return '-';
        }
        
        // Format: 0812-3456-7890
        $hp = preg_replace('/[^0-9]/', '', $this->no_hp);
        if (strlen($hp) >= 10) {
            return substr($hp, 0, 4) . '-' . substr($hp, 4, 4) . '-' . substr($hp, 8);
        }
        
        return $this->no_hp;
    }

    // Mutator untuk NISN (hapus spasi dan karakter non-numeric)
    public function setNisnAttribute($value)
    {
        $this->attributes['nisn'] = preg_replace('/[^0-9]/', '', $value);
    }

    // Mutator untuk nama lengkap (trim dan capitalize)
    public function setNamaLengkapAttribute($value)
    {
        $this->attributes['nama_lengkap'] = ucwords(strtolower(trim($value)));
    }

    // ==================== QUERY METHODS ====================
    
    // Method untuk mendapatkan teman sekelas
    public function getTemanSekelas()
    {
        return self::where('kelas_id', $this->kelas_id)
                  ->where('id', '!=', $this->id)
                  ->orderBy('nama_lengkap')
                  ->get();
    }

    // Method untuk mendapatkan absensi dalam rentang tanggal
    public function getAbsensiPeriode($startDate, $endDate)
    {
        return $this->absensiSiswa()
                   ->whereBetween('tanggal', [$startDate, $endDate])
                   ->orderBy('tanggal', 'desc')
                   ->get();
    }

    // Method untuk mendapatkan rekap absensi bulanan
    public function getRekapBulanan($tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        return $this->absensiSiswa()
                   ->whereYear('tanggal', $tahun)
                   ->whereMonth('tanggal', $bulan)
                   ->selectRaw('status, COUNT(*) as jumlah')
                   ->groupBy('status')
                   ->pluck('jumlah', 'status')
                   ->toArray();
    }

    // ==================== VALIDATION METHODS ====================
    
    // Method untuk validasi apakah bisa mencatat absensi
    public function bolehMencatatAbsensi()
    {
        return $this->isKetuaAtauWakil();
    }

    // Method untuk validasi apakah dalam kelas yang sama
    public function samaKelas($siswaLain)
    {
        if ($siswaLain instanceof self) {
            return $this->kelas_id === $siswaLain->kelas_id;
        }
        return false;
    }

    // ==================== BOOT METHOD ====================
    
    protected static function boot()
    {
        parent::boot();
        
        // Event saat siswa dibuat
        static::creating(function ($siswa) {
            // Pastikan hanya ada 1 ketua kelas per kelas
            if ($siswa->is_ketua_kelas) {
                self::where('kelas_id', $siswa->kelas_id)
                    ->where('is_ketua_kelas', true)
                    ->update(['is_ketua_kelas' => false]);
            }
            
            // Pastikan hanya ada 1 wakil ketua kelas per kelas
            if ($siswa->is_wakil_ketua) {
                self::where('kelas_id', $siswa->kelas_id)
                    ->where('is_wakil_ketua', true)
                    ->update(['is_wakil_ketua' => false]);
            }
        });
        
        // Event saat siswa diupdate
        static::updating(function ($siswa) {
            if ($siswa->isDirty('is_ketua_kelas') && $siswa->is_ketua_kelas) {
                self::where('kelas_id', $siswa->kelas_id)
                    ->where('id', '!=', $siswa->id)
                    ->where('is_ketua_kelas', true)
                    ->update(['is_ketua_kelas' => false]);
            }
            
            if ($siswa->isDirty('is_wakil_ketua') && $siswa->is_wakil_ketua) {
                self::where('kelas_id', $siswa->kelas_id)
                    ->where('id', '!=', $siswa->id)
                    ->where('is_wakil_ketua', true)
                    ->update(['is_wakil_ketua' => false]);
            }
        });
    }
}















// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Siswa extends Model
// {
//     use HasFactory;

//     protected $table = 'siswa';
    
//     protected $fillable = [
//         'user_id',
//         'nisn',
//         'nama_lengkap',
//         'kelas_id',
//         'jenis_kelamin',
//         'no_hp',
//         'alamat',
//         'is_ketua_kelas',
//         'is_wakil_ketua'
//     ];

//     protected $casts = [
//         'is_ketua_kelas' => 'boolean',
//         'is_wakil_ketua' => 'boolean'
//     ];

//     // Relasi ke User
//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     // Relasi ke Kelas
//     public function kelas()
//     {
//         return $this->belongsTo(Kelas::class);
//     }

//     // Relasi ke Materi Pembelajaran
//     public function materiPembelajaran()
//     {
//         return $this->hasMany(MateriPembelajaran::class);
//     }

//     // Helper method
//     public function isKetuaAtauWakil()
//     {
//         return $this->is_ketua_kelas || $this->is_wakil_ketua;
//     }
// }