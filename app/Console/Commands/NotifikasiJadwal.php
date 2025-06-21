<?
namespace App\Console\Commands;

use App\Models\JadwalPelajaran;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotifikasiJadwal extends Command
{
    protected $signature = 'notifikasi:jadwal';
    protected $description = 'Kirim notifikasi pengingat 15 menit sebelum jadwal mengajar';
    
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }
    
    public function handle()
    {
        //   try {
        //     $updates = Telegram::getUpdates(['limit' => 10, 'timeout' => 0]);
        //     $this->info('Updates: ' . json_encode($updates, JSON_PRETTY_PRINT));
            
        //     if (empty($updates)) {
        //         $this->warn('Tidak ada update terbaru. Pastikan bot Anda sudah diinteraksi.');
        //         $this->warn('Coba kirim /start ke bot Anda.');
        //     }
            
        //     return 0;
        // } catch (\Exception $e) {
        //     $this->error('Error: ' . $e->getMessage());
        //     return 1;
        // }

        $now = Carbon::now();
        $hari = $now->locale('id')->dayName;
        $waktu15MenitLagi = $now->copy()->addMinutes(15)->format('H:i:00');
        
        // Cari jadwal yang akan dimulai 15 menit lagi
        $jadwalList = JadwalPelajaran::with(['guru.user', 'kelas', 'mataPelajaran'])
            ->where('hari', $hari)
            ->where('jam_mulai', $waktu15MenitLagi)
            ->get();
            
        $this->info("Mencari jadwal yang akan dimulai 15 menit lagi pada hari {$hari} jam {$waktu15MenitLagi}");
        $this->info("Ditemukan {$jadwalList->count()} jadwal");
            
        foreach ($jadwalList as $jadwal) {
            if ($jadwal->guru && $jadwal->guru->user && $jadwal->guru->user->telegram_chat_id) {
                $result = $this->telegramService->sendNotificationToGuru($jadwal->guru, $jadwal);
                if ($result) {
                    $this->info("Notifikasi pengingat terkirim ke: {$jadwal->guru->nama_lengkap}");
                } else {
                    $this->error("Gagal mengirim notifikasi pengingat ke: {$jadwal->guru->nama_lengkap}");
                }
            } else {
                $this->warn("Guru {$jadwal->guru->nama_lengkap} belum menghubungkan Telegram");
            }
        }
        
        $this->info("Total notifikasi pengingat terkirim: {$jadwalList->count()}");
    }
}