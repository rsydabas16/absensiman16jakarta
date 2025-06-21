<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';
    
    protected $fillable = [
        'guru_id',
        'jadwal_pelajaran_id',
        'tanggal',
        'jam_absen',
        'status',
        'alasan',
        'tugas',
        'qr_code',
        'is_auto_alfa'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_absen' => 'datetime:H:i',
        'is_auto_alfa' => 'boolean'
    ];

    // Status constants
    const STATUS_HADIR = 'hadir';
    const STATUS_TIDAK_HADIR = 'tidak_hadir';
    const STATUS_IZIN = 'izin';
    const STATUS_SAKIT = 'sakit';
    const STATUS_DINAS_LUAR = 'dinas_luar';
    const STATUS_CUTI = 'cuti';

    // Relasi ke Guru
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    // Relasi ke Jadwal Pelajaran
    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class);
    }

    // Relasi ke Materi Pembelajaran
    public function materiPembelajaran()
    {
        return $this->hasOne(MateriPembelajaran::class);
    }

    // Scope untuk status tertentu
    public function scopeHadir($query)
    {
        return $query->where('status', self::STATUS_HADIR);
    }

    public function scopeTidakHadir($query)
    {
        return $query->where('status', self::STATUS_TIDAK_HADIR);
    }

    public function scopeIzin($query)
    {
        return $query->where('status', self::STATUS_IZIN);
    }

    public function scopeSakit($query)
    {
        return $query->where('status', self::STATUS_SAKIT);
    }

    public function scopeDinasLuar($query)
    {
        return $query->where('status', self::STATUS_DINAS_LUAR);
    }

    public function scopeCuti($query)
    {
        return $query->where('status', self::STATUS_CUTI);
    }

    // Helper method untuk cek status
    public function isHadir()
    {
        return $this->status === self::STATUS_HADIR;
    }

    public function isTidakHadir()
    {
        return $this->status === self::STATUS_TIDAK_HADIR;
    }

    // Boot method untuk generate QR Code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->qr_code)) {
                $model->qr_code = \Str::random(32);
            }
        });
    }

    // Getter untuk status label
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_TIDAK_HADIR => 'Tidak Hadir (Alfa)',
            self::STATUS_IZIN => 'Izin',
            self::STATUS_SAKIT => 'Sakit',
            self::STATUS_DINAS_LUAR => 'Dinas Luar',
            self::STATUS_CUTI => 'Cuti'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    // Getter untuk status badge class
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            self::STATUS_HADIR => 'success',
            self::STATUS_TIDAK_HADIR => 'danger',
            self::STATUS_IZIN => 'info',
            self::STATUS_SAKIT => 'warning',
            self::STATUS_DINAS_LUAR => 'primary',
            self::STATUS_CUTI => 'secondary'
        ];

        return $classes[$this->status] ?? 'secondary';
    }
}

// app/Models/AbsensiGuru.php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class AbsensiGuru extends Model
// {
//     use HasFactory;

//     protected $table = 'absensi_guru';
    
//     protected $fillable = [
//         'guru_id',
//         'jadwal_pelajaran_id',
//         'tanggal',
//         'jam_absen',
//         'status',
//         'alasan',
//         'tugas',
//         'qr_code'
//     ];

//     protected $casts = [
//         'tanggal' => 'date',
//         'jam_absen' => 'datetime:H:i'
//     ];

//     // Relasi ke Guru
//     public function guru()
//     {
//         return $this->belongsTo(Guru::class);
//     }

//     // Relasi ke Jadwal Pelajaran
//     public function jadwalPelajaran()
//     {
//         return $this->belongsTo(JadwalPelajaran::class);
//     }

//     // Relasi ke Materi Pembelajaran
//     public function materiPembelajaran()
//     {
//         return $this->hasOne(MateriPembelajaran::class);
//     }

//     // Boot method untuk generate QR Code
//     protected static function boot()
//     {
//         parent::boot();

//         static::creating(function ($model) {
//             $model->qr_code = \Str::random(32);
//         });
//     }
// }

// app/Models/AbsensiGuru.php
