<?php
// app/Models/HariLibur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_libur';
    
    protected $fillable = [
        'tanggal',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    // Static method untuk cek hari libur
    public static function isHariLibur($tanggal)
    {
        return self::where('tanggal', $tanggal)->exists();
    }
}