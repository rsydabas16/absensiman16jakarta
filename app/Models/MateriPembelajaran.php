<?php
// app/Models/MateriPembelajaran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'materi_pembelajaran';
    
    protected $fillable = [
        'absensi_guru_id',
        'siswa_id',
        'materi'
    ];

    // Relasi ke Absensi Guru
    public function absensiGuru()
    {
        return $this->belongsTo(AbsensiGuru::class);
    }

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}