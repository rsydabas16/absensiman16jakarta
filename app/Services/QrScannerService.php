<?php
// app/Services/QrScannerService.php

namespace App\Services;

use App\Models\AbsensiGuru;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class QrScannerService
{
    public function generateQrCode($jadwalId)
    {
        $tanggal = now()->format('Y-m-d');
        $random = \Str::random(16);
        $qrContent = "{$jadwalId}_{$tanggal}_{$random}";
        
        // Simpan di cache selama 15 menit
        Cache::put("qr_{$qrContent}", true, now()->addMinutes(15));
        
        return $qrContent;
    }
    
    public function validateQrCode($qrCode, $jadwalId)
    {
        // Cek apakah QR masih valid di cache
        if (!Cache::has("qr_{$qrCode}")) {
            return false;
        }
        
        $parts = explode('_', $qrCode);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        [$qrJadwalId, $qrTanggal, $random] = $parts;
        
        // Validasi jadwal ID
        if ($qrJadwalId != $jadwalId) {
            return false;
        }
        
        // Validasi tanggal
        if ($qrTanggal !== now()->format('Y-m-d')) {
            return false;
        }
        
        // Hapus dari cache setelah digunakan
        Cache::forget("qr_{$qrCode}");
        
        return true;
    }
}