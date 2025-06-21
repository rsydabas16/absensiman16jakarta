<?php
// app/Models/MataPelajaran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';
    
    protected $fillable = [
        'nama_mata_pelajaran',
        'kode_mapel'
    ];

    // Relasi ke Jadwal Pelajaran
    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
}