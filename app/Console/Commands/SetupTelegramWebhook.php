<?php
// app/Console/Commands/SetupTelegramWebhook.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetupTelegramWebhook extends Command
{
    protected $signature = 'telegram:setup-webhook';
    protected $description = 'Setup webhook untuk Telegram bot';
    
    public function handle()
    {
        $url = config('telegram.bots.mybot.webhook_url');
        
        if (empty($url)) {
            $this->error('Webhook URL tidak ditemukan di config. Set TELEGRAM_WEBHOOK_URL di .env');
            return 1;
        }
        
        try {
            $response = Telegram::setWebhook(['url' => $url]);
            $this->info('Webhook berhasil disetup: ' . $url);
            $this->info('Response: ' . json_encode($response));
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    

}
