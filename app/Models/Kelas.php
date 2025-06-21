<?php
// app/Models/Kelas.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    
    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan'
    ];

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    // Relasi ke Jadwal Pelajaran
    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    // Helper method
    public function getNamaLengkap()
    {
        return $this->tingkat . ' ' . $this->jurusan . ' ' . $this->nama_kelas;
    }








    // ==================== UPDATE MODEL KELAS ====================
// Tambahkan ke app/Models/Kelas.php

// Relasi ke Absensi Siswa
public function absensiSiswa()
{
    return $this->hasMany(AbsensiSiswa::class);
}

// Method untuk mendapatkan statistik absensi kelas
public function getStatistikAbsensi($startDate = null, $endDate = null)
{
    return AbsensiSiswa::getStatistikKelas($this->id, $startDate, $endDate);
}

// Method untuk mendapatkan total siswa dalam kelas
public function getTotalSiswa()
{
    return $this->siswa()->count();
}

// Method untuk mendapatkan ketua kelas
public function getKetuaKelas()
{
    return $this->siswa()->where('is_ketua_kelas', true)->first();
}

// Method untuk mendapatkan wakil ketua kelas
public function getWakilKelas()
{
    return $this->siswa()->where('is_wakil_ketua', true)->first();
}

// Method untuk mendapatkan persentase kehadiran kelas
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
}