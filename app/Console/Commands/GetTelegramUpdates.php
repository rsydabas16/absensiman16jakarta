<?

// app/Console/Commands/GetTelegramUpdates.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class GetTelegramUpdates extends Command
{
    protected $signature = 'telegram:updates';
    protected $description = 'Dapatkan update terbaru dari Telegram';
    
    public function handle()
    {
        try {
            $updates = Telegram::getUpdates(['limit' => 10, 'timeout' => 0]);
            $this->info('Updates: ' . json_encode($updates, JSON_PRETTY_PRINT));
            
            if (empty($updates)) {
                $this->warn('Tidak ada update terbaru. Pastikan bot Anda sudah diinteraksi.');
                $this->warn('Coba kirim /start ke bot Anda.');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}