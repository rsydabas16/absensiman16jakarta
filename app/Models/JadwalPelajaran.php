<?php
// app/Models/JadwalPelajaran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pelajaran';
    
    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mata_pelajaran_id',
        'hari',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'tahun_ajaran',
        'semester'
    ];

    protected $casts = [
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i'
    ];

    // Relasi ke Guru
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi ke Mata Pelajaran
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    // Relasi ke Absensi Guru
    public function absensiGuru()
    {
        return $this->hasMany(AbsensiGuru::class);
    }

    // Helper method
    public function getJadwalLengkap()
    {
        return $this->hari . ' Jam ' . $this->jam_ke . ' (' . $this->jam_mulai . ' - ' . $this->jam_selesai . ')';
    }
}