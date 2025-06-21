<?php





//app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nomor_induk',
        'name',
        'email',
        'password',
        'role',
        'telegram_chat_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi ke Guru
    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    // Helper methods
    public function isGuru()
    {
        return $this->role === 'guru';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }

    public function isKepalaSekolah()
    {
        return $this->role === 'kepala_sekolah';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

















    // Helper method untuk mendapatkan data profile berdasarkan role
public function getProfile()
{
    switch ($this->role) {
        case 'siswa':
            return $this->siswa;
        case 'guru':
            return $this->guru;
        default:
            return null;
    }
}

// Helper method untuk cek apakah siswa adalah ketua/wakil kelas
public function isKetuaAtauWakilKelas()
{
    if ($this->role !== 'siswa' || !$this->siswa) {
        return false;
    }
    
    return $this->siswa->isKetuaAtauWakil();
}
}












