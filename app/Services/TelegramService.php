<?php
// app/Services/TelegramService.php

// ============================================================================
// 3. TELEGRAM SERVICE - VERSI LENGKAP
// ============================================================================
// app/Services/TelegramService.php



namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramService
{
    /**
     * Kirim pesan Telegram dengan retry mechanism
     *
     * @param string $chatId Chat ID pengguna Telegram
     * @param string $message Pesan yang akan dikirim
     * @param int $retryCount Jumlah percobaan ulang
     * @return bool
     */
    public function sendMessage($chatId, $message, $retryCount = 3)
    {
        for ($i = 0; $i < $retryCount; $i++) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true
                ]);
                
                Log::info("Telegram message sent successfully to chat_id: {$chatId}");
                return true;
                
            } catch (\Exception $e) {
                Log::error("Telegram Error (Attempt " . ($i + 1) . "): " . $e->getMessage());
                
                if ($i < $retryCount - 1) {
                    sleep(1); // Wait 1 second before retry
                }
            }
        }
        
        return false;
    }
    
    /**
     * Kirim notifikasi ke guru tentang jadwal mengajar (15 menit sebelum)
     *
     * @param \App\Models\Guru $guru
     * @param \App\Models\JadwalPelajaran $jadwal
     * @return bool
     */
    public function sendNotificationToGuru($guru, $jadwal)
    {
        if (!$guru->user || !$guru->user->telegram_chat_id) {
            Log::warning("Guru {$guru->nama_lengkap} doesn't have telegram_chat_id");
            return false;
        }
        
        $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran ?? 'Mata Pelajaran';
        $kelas = $jadwal->kelas->nama_kelas ?? 'Kelas';
        $jamMulai = $this->formatTime($jadwal->jam_mulai);
        $jamSelesai = $this->formatTime($jadwal->jam_selesai);
        
        $message = "🔔 *PENGINGAT JADWAL MENGAJAR*\n\n";
        $message .= "Anda memiliki jadwal mengajar saat ini:\n\n";
        $message .= "📚 Mata Pelajaran: *{$mapel}*\n";
        $message .= "🏫 Kelas: *{$kelas}*\n";
        $message .= "⏰ Waktu: *{$jamMulai}";
        if ($jamSelesai) {
            $message .= " - {$jamSelesai}";
        }
        $message .= "* (Jam ke-{$jadwal->jam_ke})\n";
        // $message .= "📍 Ruang: " . ($jadwal->ruang ?? 'Belum ditentukan') . "\n\n";
        $message .= "💡 Jangan lupa untuk melakukan absensi!\n";
        $message .= "📱 Akses absensi: " . url('/guru/absensi');
        
        return $this->sendMessage($guru->user->telegram_chat_id, $message);
    }
    
    /**
     * Kirim notifikasi ketika jadwal mengajar dimulai
     *
     * @param \App\Models\Guru $guru
     * @param \App\Models\JadwalPelajaran $jadwal
     * @return bool
     */
    public function sendJadwalDimulaiNotification($guru, $jadwal)
    {
        if (!$guru->user || !$guru->user->telegram_chat_id) {
            Log::warning("Guru {$guru->nama_lengkap} doesn't have telegram_chat_id");
            return false;
        }
        
        $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran ?? 'Mata Pelajaran';
        $kelas = $jadwal->kelas->nama_kelas ?? 'Kelas';
        $jamMulai = $this->formatTime($jadwal->jam_mulai);
        $jamSelesai = $this->formatTime($jadwal->jam_selesai);
        
        $message = "🚀 *JADWAL MENGAJAR DIMULAI SEKARANG!*\n\n";
        $message .= "Waktunya mengajar:\n\n";
        $message .= "📚 Mata Pelajaran: *{$mapel}*\n";
        $message .= "🏫 Kelas: *{$kelas}*\n";
        $message .= "⏰ Waktu: *{$jamMulai}";
        if ($jamSelesai) {
            $message .= " - {$jamSelesai}";
        }
        $message .= "* (Jam ke-{$jadwal->jam_ke})\n";
        // $message .= "📍 Ruang: " . ($jadwal->ruang ?? 'Belum ditentukan') . "\n\n";
        $message .= "✅ Segera lakukan absensi dan mulai pembelajaran!\n";
        $message .= "📱 Akses absensi: " . url('/guru/absensi');
        
        return $this->sendMessage($guru->user->telegram_chat_id, $message);
    }
    
    /**
     * Kirim notifikasi terlambat ke guru
     *
     * @param \App\Models\Guru $guru
     * @param \App\Models\JadwalPelajaran $jadwal
     * @return bool
     */
    public function sendTerlambatNotificationToGuru($guru, $jadwal)
    {
        if (!$guru->user || !$guru->user->telegram_chat_id) {
            Log::warning("Guru {$guru->nama_lengkap} doesn't have telegram_chat_id");
            return false;
        }
        
        $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran ?? 'Mata Pelajaran';
        $kelas = $jadwal->kelas->nama_kelas ?? 'Kelas';
        $jamMulai = $this->formatTime($jadwal->jam_mulai);
        $lateMinutes = config('telegram.notifications.late_minutes', 10);
        
        $message = "⚠️ *PERINGATAN KETERLAMBATAN*\n\n";
        $message .= "Anda belum melakukan absensi untuk:\n\n";
        $message .= "📚 Mata Pelajaran: *{$mapel}*\n";
        $message .= "🏫 Kelas: *{$kelas}*\n";
        $message .= "⏰ Jadwal: *{$jamMulai}* (Jam ke-{$jadwal->jam_ke})\n\n";
        $message .= "🕐 Jadwal telah dimulai {$lateMinutes} menit yang lalu.\n";
        $message .= "⚡ Mohon segera lakukan absensi atau konfirmasi ketidakhadiran!\n\n";
        $message .= "📱 Akses absensi: " . url('/guru/absensi');
        
        return $this->sendMessage($guru->user->telegram_chat_id, $message);
    }
    
    /**
     * Kirim notifikasi terlambat ke siswa
     *
     * @param \App\Models\JadwalPelajaran $jadwal
     * @return bool
     */
    public function sendTerlambatNotificationToStudents($jadwal)
    {
        $kelas = $jadwal->kelas;
        $mapel = $jadwal->mataPelajaran;
        $guru = $jadwal->guru;
        $jamMulai = $this->formatTime($jadwal->jam_mulai);
        $lateMinutes = config('telegram.notifications.late_minutes', 10);
        
        $pesan = "⏰ *PEMBERITAHUAN KETERLAMBATAN GURU*\n\n";
        $pesan .= "Guru: *{$guru->nama_lengkap}*\n";
        $pesan .= "Mata Pelajaran: *{$mapel->nama_mata_pelajaran}*\n";
        $pesan .= "Kelas: *{$kelas->nama_kelas}*\n";
        $pesan .= "Jadwal: *{$jamMulai}* (Jam ke-{$jadwal->jam_ke})\n\n";
        $pesan .= "🔄 Guru belum melakukan absensi setelah {$lateMinutes} menit.\n";
        $pesan .= "📋 Mohon tetap tertib dan menunggu konfirmasi lebih lanjut.\n";
        $pesan .= "📞 Jika diperlukan, ketua kelas dapat menghubungi guru atau tata usaha.";
        
        // Kirim ke ketua kelas
        $ketuaKelas = $kelas->siswa()
            ->whereHas('user', function ($q) {
                $q->whereNotNull('telegram_chat_id');
            })
            ->where('is_ketua_kelas', true)
            ->first();
            
        if ($ketuaKelas && $ketuaKelas->user && $ketuaKelas->user->telegram_chat_id) {
            return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
        }
        
        Log::warning("Ketua kelas tidak ditemukan atau belum menghubungkan Telegram untuk kelas: {$kelas->nama_kelas}");
        return false;
    }
    
    /**
     * Kirim notifikasi ke siswa ketika guru tidak hadir
     *
     * @param \App\Models\AbsensiGuru $absensi
     * @return bool
     */
    public function notifikasiGuruTidakHadir($absensi)
    {
        $jadwal = $absensi->jadwalPelajaran;
        $kelas = $jadwal->kelas;
        $mapel = $jadwal->mataPelajaran;
        $guru = $absensi->guru;
        
        $statusLabels = [
            'izin' => 'Izin',
            'sakit' => 'Sakit', 
            'dinas_luar' => 'Dinas Luar',
            'cuti' => 'Cuti',
            'tidak_hadir' => 'Tidak Hadir'
        ];
        
        $jamMulai = $this->formatTime($jadwal->jam_mulai);
        $jamSelesai = $this->formatTime($jadwal->jam_selesai);
        
        $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
        $pesan .= "Guru: *{$guru->nama_lengkap}*\n";
        $pesan .= "Mata Pelajaran: *{$mapel->nama_mata_pelajaran}*\n";
        $pesan .= "Kelas: *{$kelas->nama_kelas}*\n";
        $pesan .= "Waktu: *{$jamMulai}";
        if ($jamSelesai) {
            $pesan .= " - {$jamSelesai}";
        }
        $pesan .= "* (Jam ke-{$jadwal->jam_ke})\n";
        $pesan .= "Status: *" . ($statusLabels[$absensi->status] ?? ucfirst($absensi->status)) . "*\n";
        
        if ($absensi->alasan) {
            $pesan .= "Alasan: {$absensi->alasan}\n";
        }
        
        if ($absensi->tugas) {
            $pesan .= "\n📝 *TUGAS DARI GURU:*\n{$absensi->tugas}\n";
            $pesan .= "\n💡 Mohon kerjakan tugas dengan baik dan tertib.";
        } else {
            $pesan .= "\n📖 Silakan gunakan waktu untuk belajar mandiri atau mengerjakan tugas lain.";
        }
        
        $pesan .= "\n\n📋 Ketua kelas mohon catat dan laporkan ke wali kelas jika diperlukan.";
        
        // Kirim ke ketua kelas
        $ketuaKelas = $kelas->siswa()
            ->whereHas('user', function ($q) {
                $q->whereNotNull('telegram_chat_id');
            })
            ->where('is_ketua_kelas', true)
            ->first();
            
        if ($ketuaKelas && $ketuaKelas->user && $ketuaKelas->user->telegram_chat_id) {
            return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
        }
        
        Log::warning("Ketua kelas tidak ditemukan atau belum menghubungkan Telegram untuk kelas: {$kelas->nama_kelas}");
        return false;
    }
    
    /**
     * Kirim notifikasi otomatis alfa
     *
     * @param \App\Models\JadwalPelajaran $jadwal
     * @return bool
     */
    public function sendAutoAlfaNotification($jadwal)
    {
        $kelas = $jadwal->kelas;
        $mapel = $jadwal->mataPelajaran;
        $guru = $jadwal->guru;
        
        $jamMulai = $this->formatTime($jadwal->jam_mulai);
        $jamSelesai = $this->formatTime($jadwal->jam_selesai);
        
        $pesan = "⚠️ *PEMBERITAHUAN OTOMATIS*\n\n";
        $pesan .= "Guru: *{$guru->nama_lengkap}*\n";
        $pesan .= "Mata Pelajaran: *{$mapel->nama_mata_pelajaran}*\n";
        $pesan .= "Kelas: *{$kelas->nama_kelas}*\n";
        $pesan .= "Waktu: *{$jamMulai}";
        if ($jamSelesai) {
            $pesan .= " - {$jamSelesai}";
        }
        $pesan .= "* (Jam ke-{$jadwal->jam_ke})\n";
        $pesan .= "Status: *Tidak Hadir (Otomatis)*\n\n";
        $pesan .= "🤖 Sistem otomatis mencatat ketidakhadiran karena guru tidak melakukan absensi dalam waktu yang ditentukan.\n";
        $pesan .= "📋 Ketua kelas mohon catat dan laporkan ke wali kelas atau tata usaha.";
        
        // Kirim ke ketua kelas
        $ketuaKelas = $kelas->siswa()
            ->whereHas('user', function ($q) {
                $q->whereNotNull('telegram_chat_id');
            })
            ->where('is_ketua_kelas', true)
            ->first();
            
        if ($ketuaKelas && $ketuaKelas->user && $ketuaKelas->user->telegram_chat_id) {
            return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
        }
        
        Log::warning("Ketua kelas tidak ditemukan atau belum menghubungkan Telegram untuk kelas: {$kelas->nama_kelas}");
        return false;
    }
    
    /**
     * Kirim laporan harian absensi
     *
     * @param array $data
     * @return bool
     */
    public function sendDailyReport($data)
    {
        $tanggal = $data['tanggal'];
        $totalJadwal = $data['total_jadwal'];
        $hadir = $data['hadir'];
        $tidakHadir = $data['tidak_hadir'];
        $izin = $data['izin'];
        $sakit = $data['sakit'];
        $dinasLuar = $data['dinas_luar'];
        $cuti = $data['cuti'];
        $autoAlfa = $data['auto_alfa'];
        
        $message = "📊 *LAPORAN ABSENSI HARIAN*\n";
        $message .= "📅 Tanggal: *{$tanggal}*\n\n";
        $message .= "📋 *STATISTIK:*\n";
        $message .= "• Total Jadwal: {$totalJadwal}\n";
        $message .= "• Hadir: ✅ {$hadir}\n";
        $message .= "• Tidak Hadir: ❌ {$tidakHadir}\n";
        $message .= "• Izin: 📄 {$izin}\n";
        $message .= "• Sakit: 🤒 {$sakit}\n";
        $message .= "• Dinas Luar: 🏢 {$dinasLuar}\n";
        $message .= "• Cuti: 🏖️ {$cuti}\n";
        $message .= "• Auto Alfa: 🤖 {$autoAlfa}\n\n";
        
        $persentaseHadir = $totalJadwal > 0 ? round(($hadir / $totalJadwal) * 100, 1) : 0;
        $message .= "📈 Tingkat Kehadiran: *{$persentaseHadir}%*\n\n";
        
        if ($persentaseHadir >= 90) {
            $message .= "🎉 Tingkat kehadiran sangat baik!";
        } elseif ($persentaseHadir >= 80) {
            $message .= "👍 Tingkat kehadiran baik.";
        } elseif ($persentaseHadir >= 70) {
            $message .= "⚠️ Tingkat kehadiran perlu ditingkatkan.";
        } else {
            $message .= "🚨 Tingkat kehadiran rendah, perlu perhatian khusus!";
        }
        
        // Kirim ke admin/kepala sekolah
        $adminChatId = config('telegram.admin_chat_id');
        if ($adminChatId) {
            return $this->sendMessage($adminChatId, $message);
        }
        
        Log::warning("Admin chat ID not configured for daily report");
        return false;
    }
    
    /**
     * Format waktu untuk tampilan yang konsisten
     *
     * @param mixed $time
     * @return string
     */
    private function formatTime($time)
    {
        if (empty($time)) {
            return 'Belum ditentukan';
        }

        try {
            // Jika sudah dalam format Carbon
            if ($time instanceof Carbon) {
                return $time->format('H:i');
            }
            
            // Jika dalam format string
            if (is_string($time)) {
                // Coba parse dengan berbagai format
                if (preg_match('/^(\d{1,2}):(\d{2})$/', $time)) {
                    return Carbon::createFromFormat('H:i', $time)->format('H:i');
                } elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time)) {
                    return Carbon::createFromFormat('H:i:s', $time)->format('H:i');
                } else {
                    return Carbon::parse($time)->format('H:i');
                }
            }
            
            return (string) $time;
            
        } catch (\Exception $e) {
            Log::error("Error formatting time: " . $e->getMessage());
            return 'Format tidak valid';
        }
    }
}




// namespace App\Services;

// use Telegram\Bot\Laravel\Facades\Telegram;
// use Illuminate\Support\Facades\Log;

// class TelegramService
// {
//     /**
//      * Kirim pesan Telegram
//      *
//      * @param string $chatId Chat ID pengguna Telegram
//      * @param string $message Pesan yang akan dikirim
//      * @return bool
//      */
//     public function sendMessage($chatId, $message)
//     {
//         try {
//             Telegram::sendMessage([
//                 'chat_id' => $chatId,
//                 'text' => $message,
//                 'parse_mode' => 'Markdown'
//             ]);
            
//             return true;
//         } catch (\Exception $e) {
//             Log::error('Telegram Error: ' . $e->getMessage());
//             return false;
//         }
//     }
    
//     /**
//      * Kirim notifikasi ke guru tentang jadwal mengajar (15 menit sebelum)
//      *
//      * @param \App\Models\Guru $guru
//      * @param \App\Models\JadwalPelajaran $jadwal
//      * @return bool
//      */
//     public function sendNotificationToGuru($guru, $jadwal)
//     {
//         if (!$guru->user->telegram_chat_id) {
//             return false;
//         }
        
//         $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran;
//         $kelas = $jadwal->kelas->nama_kelas;
//         $jamMulai = $jadwal->jam_mulai->format('H:i');
        
//         $message = "🔔 *PENGINGAT JADWAL MENGAJAR*\n\n";
//         $message .= "Anda memiliki jadwal mengajar dalam 15 menit:\n\n";
//         $message .= "📚 Mata Pelajaran: *{$mapel}*\n";
//         $message .= "🏫 Kelas: *{$kelas}*\n";
//         $message .= "⏰ Waktu: *{$jamMulai}* (Jam ke-{$jadwal->jam_ke})\n";
//         $message .= "📍 Ruang: " . ($jadwal->ruang ?? 'Belum ditentukan') . "\n\n";
//         $message .= "💡 Jangan lupa untuk melakukan absensi saat tiba di kelas!";
        
//         return $this->sendMessage($guru->user->telegram_chat_id, $message);
//     }
    
//     /**
//      * Kirim notifikasi ketika jadwal mengajar dimulai
//      *
//      * @param \App\Models\Guru $guru
//      * @param \App\Models\JadwalPelajaran $jadwal
//      * @return bool
//      */
//     public function sendJadwalDimulaiNotification($guru, $jadwal)
//     {
//         if (!$guru->user->telegram_chat_id) {
//             return false;
//         }
        
//         $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran;
//         $kelas = $jadwal->kelas->nama_kelas;
//         $jamMulai = $jadwal->jam_mulai->format('H:i');
//         $jamSelesai = $jadwal->jam_selesai ? $jadwal->jam_selesai->format('H:i') : 'Belum ditentukan';
        
//         $message = "🚀 *JADWAL MENGAJAR DIMULAI SEKARANG!*\n\n";
//         $message .= "Waktunya mengajar:\n\n";
//         $message .= "📚 Mata Pelajaran: *{$mapel}*\n";
//         $message .= "🏫 Kelas: *{$kelas}*\n";
//         $message .= "⏰ Waktu: *{$jamMulai} - {$jamSelesai}* (Jam ke-{$jadwal->jam_ke})\n";
//         $message .= "📍 Ruang: " . ($jadwal->ruang ?? 'Belum ditentukan') . "\n\n";
//         $message .= "✅ Segera lakukan absensi dan mulai pembelajaran!\n";
//         $message .= "📱 Akses absensi: [Klik di sini untuk absen](https://your-domain.com/guru/absensi)";
        
//         return $this->sendMessage($guru->user->telegram_chat_id, $message);
//     }
    
//     /**
//      * Kirim notifikasi terlambat ke guru
//      *
//      * @param \App\Models\Guru $guru
//      * @param \App\Models\JadwalPelajaran $jadwal
//      * @return bool
//      */
//     public function sendTerlambatNotificationToGuru($guru, $jadwal)
//     {
//         if (!$guru->user->telegram_chat_id) {
//             return false;
//         }
        
//         $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran;
//         $kelas = $jadwal->kelas->nama_kelas;
//         $jamMulai = $jadwal->jam_mulai->format('H:i');
        
//         $message = "⚠️ *PERINGATAN KETERLAMBATAN*\n\n";
//         $message .= "Anda belum melakukan absensi untuk:\n\n";
//         $message .= "📚 Mata Pelajaran: *{$mapel}*\n";
//         $message .= "🏫 Kelas: *{$kelas}*\n";
//         $message .= "⏰ Jadwal: *{$jamMulai}* (Jam ke-{$jadwal->jam_ke})\n\n";
//         $message .= "🕐 Jadwal telah dimulai 10 menit yang lalu.\n";
//         $message .= "⚡ Mohon segera lakukan absensi atau konfirmasi ketidakhadiran!\n\n";
//         $message .= "📱 Akses absensi: [Klik di sini](https://your-domain.com/guru/absensi)";
        
//         return $this->sendMessage($guru->user->telegram_chat_id, $message);
//     }
    
//     /**
//      * Kirim notifikasi terlambat ke siswa
//      *
//      * @param \App\Models\JadwalPelajaran $jadwal
//      * @return bool
//      */
//     public function sendTerlambatNotificationToStudents($jadwal)
//     {
//         $kelas = $jadwal->kelas;
//         $mapel = $jadwal->mataPelajaran;
//         $guru = $jadwal->guru;
//         $jamMulai = $jadwal->jam_mulai->format('H:i');
        
//         $pesan = "⏰ *PEMBERITAHUAN KETERLAMBATAN GURU*\n\n";
//         $pesan .= "Guru: *{$guru->nama_lengkap}*\n";
//         $pesan .= "Mata Pelajaran: *{$mapel->nama_mata_pelajaran}*\n";
//         $pesan .= "Kelas: *{$kelas->nama_kelas}*\n";
//         $pesan .= "Jadwal: *{$jamMulai}* (Jam ke-{$jadwal->jam_ke})\n\n";
//         $pesan .= "🔄 Guru belum melakukan absensi.\n";
//         $pesan .= "📋 Mohon tetap tertib dan menunggu konfirmasi lebih lanjut.\n";
//         $pesan .= "📞 Jika diperlukan, ketua kelas dapat menghubungi guru atau tata usaha.";
        
//         // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();
            
//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
        
//         return false;
//     }
    
//     /**
//      * Kirim notifikasi ke siswa ketika guru tidak hadir
//      *
//      * @param \App\Models\AbsensiGuru $absensi
//      * @return bool
//      */
//     public function notifikasiGuruTidakHadir($absensi)
//     {
//         $jadwal = $absensi->jadwalPelajaran;
//         $kelas = $jadwal->kelas;
//         $mapel = $jadwal->mataPelajaran;
//         $guru = $absensi->guru;
        
//         $statusLabels = [
//             'izin' => 'Izin',
//             'sakit' => 'Sakit', 
//             'dinas_luar' => 'Dinas Luar',
//             'cuti' => 'Cuti',
//             'tidak_hadir' => 'Tidak Hadir'
//         ];
        
//         $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
//         $pesan .= "Guru: *{$guru->nama_lengkap}*\n";
//         $pesan .= "Mata Pelajaran: *{$mapel->nama_mata_pelajaran}*\n";
//         $pesan .= "Kelas: *{$kelas->nama_kelas}*\n";
//         $pesan .= "Waktu: *{$jadwal->jam_mulai} - {$jadwal->jam_selesai}* (Jam ke-{$jadwal->jam_ke})\n";
//         $pesan .= "Status: *" . ($statusLabels[$absensi->status] ?? ucfirst($absensi->status)) . "*\n";
        
//         if ($absensi->alasan) {
//             $pesan .= "Alasan: {$absensi->alasan}\n";
//         }
        
//         if ($absensi->tugas) {
//             $pesan .= "\n📝 *TUGAS DARI GURU:*\n{$absensi->tugas}\n";
//             $pesan .= "\n💡 Mohon kerjakan tugas dengan baik dan tertib.";
//         } else {
//             $pesan .= "\n📖 Silakan gunakan waktu untuk belajar mandiri atau mengerjakan tugas lain.";
//         }
        
//         $pesan .= "\n\n📋 Ketua kelas mohon catat dan laporkan ke wali kelas jika diperlukan.";
        
//         // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();
            
//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
        
//         return false;
//     }
    
//     /**
//      * Kirim notifikasi otomatis alfa
//      *
//      * @param \App\Models\JadwalPelajaran $jadwal
//      * @return bool
//      */
//     public function sendAutoAlfaNotification($jadwal)
//     {
//         $kelas = $jadwal->kelas;
//         $mapel = $jadwal->mataPelajaran;
//         $guru = $jadwal->guru;
        
//         $pesan = "⚠️ *PEMBERITAHUAN OTOMATIS*\n\n";
//         $pesan .= "Guru: *{$guru->nama_lengkap}*\n";
//         $pesan .= "Mata Pelajaran: *{$mapel->nama_mata_pelajaran}*\n";
//         $pesan .= "Kelas: *{$kelas->nama_kelas}*\n";
//         $pesan .= "Waktu: *{$jadwal->jam_mulai} - {$jadwal->jam_selesai}* (Jam ke-{$jadwal->jam_ke})\n";
//         $pesan .= "Status: *Tidak Hadir (Otomatis)*\n\n";
//         $pesan .= "🤖 Sistem otomatis mencatat ketidakhadiran karena guru tidak melakukan absensi dalam waktu yang ditentukan.\n";
//         $pesan .= "📋 Ketua kelas mohon catat dan laporkan ke wali kelas atau tata usaha.";
        
//         // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();
            
//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
        
//         return false;
//     }
    
//     /**
//      * Kirim laporan harian absensi
//      *
//      * @param array $data
//      * @return bool
//      */
//     public function sendDailyReport($data)
//     {
//         $tanggal = $data['tanggal'];
//         $totalJadwal = $data['total_jadwal'];
//         $hadir = $data['hadir'];
//         $tidakHadir = $data['tidak_hadir'];
//         $izin = $data['izin'];
//         $sakit = $data['sakit'];
//         $dinasLuar = $data['dinas_luar'];
//         $cuti = $data['cuti'];
//         $autoAlfa = $data['auto_alfa'];
        
//         $message = "📊 *LAPORAN ABSENSI HARIAN*\n";
//         $message .= "📅 Tanggal: *{$tanggal}*\n\n";
//         $message .= "📋 *STATISTIK:*\n";
//         $message .= "• Total Jadwal: {$totalJadwal}\n";
//         $message .= "• Hadir: ✅ {$hadir}\n";
//         $message .= "• Tidak Hadir: ❌ {$tidakHadir}\n";
//         $message .= "• Izin: 📄 {$izin}\n";
//         $message .= "• Sakit: 🤒 {$sakit}\n";
//         $message .= "• Dinas Luar: 🏢 {$dinasLuar}\n";
//         $message .= "• Cuti: 🏖️ {$cuti}\n";
//         $message .= "• Auto Alfa: 🤖 {$autoAlfa}\n\n";
        
//         $persentaseHadir = $totalJadwal > 0 ? round(($hadir / $totalJadwal) * 100, 1) : 0;
//         $message .= "📈 Tingkat Kehadiran: *{$persentaseHadir}%*\n\n";
        
//         if ($persentaseHadir >= 90) {
//             $message .= "🎉 Tingkat kehadiran sangat baik!";
//         } elseif ($persentaseHadir >= 80) {
//             $message .= "👍 Tingkat kehadiran baik.";
//         } elseif ($persentaseHadir >= 70) {
//             $message .= "⚠️ Tingkat kehadiran perlu ditingkatkan.";
//         } else {
//             $message .= "🚨 Tingkat kehadiran rendah, perlu perhatian khusus!";
//         }
        
//         // Kirim ke admin/kepala sekolah (asumsi chat_id admin disimpan di config)
//         $adminChatId = config('telegram.admin_chat_id');
//         if ($adminChatId) {
//             return $this->sendMessage($adminChatId, $message);
//         }
        
//         return false;
//     }
// }




// namespace App\Services;

// use Telegram\Bot\Laravel\Facades\Telegram;
// use Illuminate\support\Facades\Log;

// class TelegramService
// {
//     /**
//      * Kirim pesan Telegram
//      *
//      * @param string $chatId Chat ID pengguna Telegram
//      * @param string $message Pesan yang akan dikirim
//      * @return bool
//      */
//     public function sendMessage($chatId, $message)
//     {
//         try {
//             Telegram::sendMessage([
//                 'chat_id' => $chatId,
//                 'text' => $message,
//                 'parse_mode' => 'Markdown'
//             ]);
            
//             return true;
//         } catch (\Exception $e) {
//             Log::error('Telegram Error: ' . $e->getMessage());
//             return false;
//         }
//     }
    
//     /**
//      * Kirim notifikasi ke guru tentang jadwal mengajar
//      *
//      * @param \App\Models\Guru $guru
//      * @param \App\Models\JadwalPelajaran $jadwal
//      * @return bool
//      */
//     public function sendNotificationToGuru($guru, $jadwal)
//     {
//         if (!$guru->user->telegram_chat_id) {
//             return false;
//         }
        
//         $mapel = $jadwal->mataPelajaran->nama_mata_pelajaran;
//         $kelas = $jadwal->kelas->nama_kelas;
//         $jamMulai = $jadwal->jam_mulai->format('H:i');
        
//         $message = "🔔 *PENGINGAT JADWAL MENGAJAR*\n\n";
//         $message .= "Anda memiliki jadwal mengajar:\n";
//         $message .= "📚 Mata Pelajaran: {$mapel}\n";
//         $message .= "🏫 Kelas: {$kelas}\n";
//         $message .= "⏰ Jam: {$jamMulai}\n";
//         $message .= "📍 Jam ke-{$jadwal->jam_ke}\n\n";
//         $message .= "Jangan lupa untuk melakukan absensi!";
        
//         return $this->sendMessage($guru->user->telegram_chat_id, $message);
//     }
    
//     /**
//      * Kirim notifikasi ke siswa ketika guru tidak hadir
//      *
//      * @param \App\Models\AbsensiGuru $absensi
//      * @return bool
//      */
//     public function notifikasiGuruTidakHadir($absensi)
//     {
//         $jadwal = $absensi->jadwalPelajaran;
//         $kelas = $jadwal->kelas;
//         $mapel = $jadwal->mataPelajaran;
//         $guru = $absensi->guru;
        
//         $pesan = "📚 *INFORMASI KETIDAKHADIRAN GURU*\n\n";
//         $pesan .= "Guru: {$guru->nama_lengkap}\n";
//         $pesan .= "Mata Pelajaran: {$mapel->nama_mata_pelajaran}\n";
//         $pesan .= "Kelas: {$kelas->nama_kelas}\n";
//         $pesan .= "Jam ke-{$jadwal->jam_ke} ({$jadwal->jam_mulai} - {$jadwal->jam_selesai})\n";
//         $pesan .= "Status: " . ucfirst($absensi->status) . "\n";
        
//         if ($absensi->alasan) {
//             $pesan .= "Alasan: {$absensi->alasan}\n";
//         }
        
//         if ($absensi->tugas) {
//             $pesan .= "\n📝 *TUGAS:*\n{$absensi->tugas}";
//         }
        
//         // Kirim ke ketua kelas
//         $ketuaKelas = $kelas->siswa()
//             ->whereHas('user', function ($q) {
//                 $q->whereNotNull('telegram_chat_id');
//             })
//             ->where('is_ketua_kelas', true)
//             ->first();
            
//         if ($ketuaKelas && $ketuaKelas->user->telegram_chat_id) {
//             return $this->sendMessage($ketuaKelas->user->telegram_chat_id, $pesan);
//         }
        
//         return false;
//     }
// }