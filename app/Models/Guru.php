<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;
    
    protected $table = 'guru';
    
    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'no_hp',
        'alamat',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
    
    public function absensi()
    {
        return $this->hasMany(AbsensiGuru::class);
    }
}