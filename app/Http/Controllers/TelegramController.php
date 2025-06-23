<?php
// app/Http/Controllers/TelegramController.php

// app/Http/Controllers/TelegramController.php

namespace App\Http\Controllers;

use App\Models\User;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        // Log semua request yang masuk
        Log::info('=== TELEGRAM WEBHOOK CALLED ===');
        Log::info('Request IP: ' . $request->ip());
        Log::info('Request Method: ' . $request->method());
        Log::info('Request Headers: ', $request->headers->all());
        Log::info('Request Body: ', $request->all());
        
        try {
            // Validasi request dari Telegram
            if (!$this->validateTelegramRequest($request)) {
                Log::warning('Invalid Telegram request');
                return response('Unauthorized', 401);
            }
            
            $update = Telegram::commandsHandler(true);
            
            Log::info('Update diterima: ' . json_encode($update->toArray()));
            
            // Handle different types of updates
            if ($update->getMessage()) {
                $this->handleMessage($update->getMessage());
            } elseif ($update->getCallbackQuery()) {
                $this->handleCallbackQuery($update->getCallbackQuery());
            } else {
                Log::info('Update type tidak dikenali');
            }
            
            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            Log::error('=== TELEGRAM WEBHOOK ERROR ===');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error', 
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    private function validateTelegramRequest(Request $request)
    {
        // Basic validation - bisa ditambah dengan secret token validation
        $userAgent = $request->header('User-Agent', '');
        
        // Telegram biasanya mengirim dengan user agent yang mengandung "TelegramBot"
        if (app()->environment('production')) {
            return strpos($userAgent, 'TelegramBot') !== false;
        }
        
        // Di development, skip validation
        return true;
    }
    
    private function handleMessage($message)
    {
        $chatId = $message->getChat()->getId();
        $text = $message->getText() ?? '';
        $from = $message->getFrom();
        
        Log::info("=== PROCESSING MESSAGE ===");
        Log::info("Chat ID: {$chatId}");
        Log::info("From: {$from->getFirstName()} (@{$from->getUsername()})");
        Log::info("Text: {$text}");
        
        // Handle commands
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chatId, $text, $from, $message);
        } else {
            $this->handleRegularMessage($chatId, $text, $from);
        }
    }
    
    private function handleCommand($chatId, $text, $from, $message)
    {
        $command = strtolower(explode(' ', $text)[0]);
        
        Log::info("Processing command: {$command}");
        
        switch ($command) {
            case '/start':
                $this->handleStart($chatId, $from, $message);
                break;
                
            case '/connect':
                $this->handleConnect($chatId, $text, $from);
                break;
                
            case '/help':
                $this->handleHelp($chatId);
                break;
                
            case '/status':
                $this->handleStatus($chatId, $from);
                break;
                
            default:
                $this->handleUnknownCommand($chatId, $command);
        }
    }
    
    private function handleStart($chatId, $from, $message)
    {
        Log::info("=== HANDLING /start COMMAND ===");
        Log::info("Chat ID: {$chatId}");
        
        try {
            $name = $from->getFirstName();
            $username = $from->getUsername() ? "@{$from->getUsername()}" : '';
            
            $text = "🤖 *Selamat Datang di Bot Absensi Guru!*\n\n";
            $text .= "Halo *{$name}* {$username}! 👋\n\n";
            $text .= "📱 *Chat ID Anda:* `{$chatId}`\n";
            $text .= "_(Salin angka di atas untuk menghubungkan akun)_\n\n";
            $text .= "🔗 *Cara Menghubungkan Akun:*\n";
            $text .= "1. `/connect [nomor_induk]`\n\n";
            $text .= "Contoh : `/connect 221020102210001`\n\n";
            $text .= "💡 *Perintah Yang Tersedia:*\n";
            $text .= "/start - Memulai bot\n";
            $text .= "/connect [nomor_induk] - Hubungkan akun\n";
            $text .= "/help - Bantuan lengkap\n";
            $text .= "/status - Cek status koneksi\n\n";
            $text .= "📞 Jika ada masalah, hubungi administrator sekolah.";
            
            $response = Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
            
            Log::info("Start message sent successfully. Message ID: " . $response->getMessageId());
            
        } catch (\Exception $e) {
            Log::error("Error sending start message: " . $e->getMessage());
            
            // Fallback dengan pesan sederhana tanpa markdown
            try {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Selamat datang di Bot Absensi Guru!\n\nChat ID Anda: {$chatId}\nGunakan /help untuk bantuan."
                ]);
            } catch (\Exception $fallbackError) {
                Log::error("Fallback message juga gagal: " . $fallbackError->getMessage());
            }
        }
    }
    
    private function handleConnect($chatId, $text, $from)
    {
        Log::info("=== HANDLING /connect COMMAND ===");
        Log::info("Full text: {$text}");
        
        $parts = explode(' ', trim($text));
        
        if (count($parts) < 2) {
            $message = "❌ *Format salah!*\n\n";
            $message .= "Gunakan: `/connect [nomor_induk]`\n\n";
            $message .= "Contoh: `/connect 198501012010011001`";
            
            $this->sendMessage($chatId, $message);
            return;
        }
        
        $nomorInduk = trim($parts[1]);
        Log::info("Mencari user dengan nomor_induk: {$nomorInduk}");
        
        try {
            $user = User::where('nomor_induk', $nomorInduk)->first();
            
            if (!$user) {
                Log::warning("User tidak ditemukan dengan nomor_induk: {$nomorInduk}");
                
                $message = "❌ *Nomor induk tidak ditemukan!*\n\n";
                $message .= "Pastikan:\n";
                $message .= "• Nomor induk benar\n";
                $message .= "• Anda sudah terdaftar di sistem\n";
                $message .= "• Hubungi admin jika masih bermasalah";
                
                $this->sendMessage($chatId, $message);
                return;
            }
            
            Log::info("User ditemukan: {$user->name} (ID: {$user->id})");
            
            if ($user->telegram_chat_id) {
                Log::warning("User sudah memiliki telegram_chat_id: {$user->telegram_chat_id}");
                
                if ($user->telegram_chat_id == $chatId) {
                    $message = "✅ *Akun sudah terhubung!*\n\n";
                    $message .= "Nama: *{$user->name}*\n";
                    $message .= "Chat ID: `{$chatId}`\n\n";
                    $message .= "Bot siap mengirim notifikasi! 🔔";
                } else {
                    $message = "⚠️ *Akun sudah terhubung dengan Telegram lain!*\n\n";
                    $message .= "Jika ini akun Anda, hubungi administrator untuk reset koneksi.";
                }
                
                $this->sendMessage($chatId, $message);
                return;
            }
            
            // Update user dengan chat_id
            $user->telegram_chat_id = $chatId;
            $user->telegram_username = $from->getUsername();
            $user->save();
            
            Log::info("User berhasil diupdate dengan telegram_chat_id: {$chatId}");
            
            $message = "✅ *Akun berhasil terhubung!*\n\n";
            $message .= "👤 Nama: *{$user->name}*\n";
            $message .= "📱 Chat ID: `{$chatId}`\n";
            $message .= "🕐 Terhubung: " . now()->format('d/m/Y H:i') . "\n\n";
            $message .= "🔔 Bot akan mulai mengirim notifikasi:\n";
            $message .= "• Pengingat jadwal mengajar\n";
            $message .= "• Pemberitahuan keterlambatan\n";
            $message .= "• Update status absensi\n\n";
            $message .= "✨ Selamat! Sistem notifikasi aktif.";
            
            $this->sendMessage($chatId, $message);
            
        } catch (\Exception $e) {
            Log::error("Error dalam handleConnect: " . $e->getMessage());
            
            $message = "❌ *Terjadi kesalahan sistem!*\n\n";
            $message .= "Silakan coba lagi atau hubungi administrator.";
            
            $this->sendMessage($chatId, $message);
        }
    }
    
    private function handleHelp($chatId)
    {
        $message = "ℹ️ *BANTUAN BOT ABSENSI GURU*\n\n";
        $message .= "📱 *Chat ID Anda:* `{$chatId}`\n\n";
        $message .= "🔧 *Cara Penggunaan:*\n";
        $message .= "1. Pastikan terdaftar di sistem absensi\n";
        $message .= "2. Gunakan `/connect [nomor_induk]` untuk menghubungkan\n";
        $message .= "3. Bot akan mengirim notifikasi otomatis\n\n";
        $message .= "💬 *Perintah Tersedia:*\n";
        $message .= "/start - Memulai bot\n";
        $message .= "/connect [nomor_induk] - Hubungkan akun\n";
        $message .= "/help - Bantuan ini\n";
        $message .= "/status - Cek status koneksi\n\n";
        $message .= "❓ Butuh bantuan? Hubungi admin sekolah.";
        
        $this->sendMessage($chatId, $message);
    }
    
    private function handleStatus($chatId, $from)
    {
        try {
            $user = User::where('telegram_chat_id', $chatId)->first();
            
            if ($user) {
                $message = "✅ *STATUS KONEKSI*\n\n";
                $message .= "👤 Nama: *{$user->name}*\n";
                $message .= "🆔 Nomor Induk: `{$user->nomor_induk}`\n";
                $message .= "📱 Chat ID: `{$chatId}`\n";
                $message .= "🔗 Status: *Terhubung* ✅\n\n";
                $message .= "🔔 Bot siap mengirim notifikasi!";
            } else {
                $message = "❌ *BELUM TERHUBUNG*\n\n";
                $message .= "📱 Chat ID: `{$chatId}`\n";
                $message .= "🔗 Status: *Belum terhubung* ❌\n\n";
                $message .= "💡 Gunakan `/connect [nomor_induk]` untuk menghubungkan akun.";
            }
            
            $this->sendMessage($chatId, $message);
            
        } catch (\Exception $e) {
            Log::error("Error dalam handleStatus: " . $e->getMessage());
            $this->sendMessage($chatId, "❌ Terjadi kesalahan saat mengecek status.");
        }
    }
    
    private function handleUnknownCommand($chatId, $command)
    {
        $message = "❓ *Perintah tidak dikenali:* `{$command}`\n\n";
        $message .= "💡 Gunakan `/help` untuk melihat perintah yang tersedia.";
        
        $this->sendMessage($chatId, $message);
    }
    
    private function handleRegularMessage($chatId, $text, $from)
    {
        Log::info("Handling regular message: {$text}");
        
        // Auto-response untuk pesan biasa
        $responses = [
            'hai' => 'Halo! 👋 Gunakan /help untuk bantuan.',
            'hello' => 'Hello! 👋 Gunakan /help untuk bantuan.',
            'help' => 'Gunakan /help untuk melihat bantuan lengkap.',
            'bantuan' => 'Gunakan /help untuk melihat bantuan lengkap.',
        ];
        
        $lowerText = strtolower($text);
        
        foreach ($responses as $trigger => $response) {
            if (strpos($lowerText, $trigger) !== false) {
                $this->sendMessage($chatId, $response);
                return;
            }
        }
        
        // Default response untuk pesan yang tidak dikenali
        $message = "🤖 Halo! Saya bot notifikasi absensi guru.\n\n";
        $message .= "Gunakan /help untuk melihat perintah yang tersedia.";
        
        $this->sendMessage($chatId, $message);
    }
    
    private function handleCallbackQuery($callbackQuery)
    {
        // Handle inline keyboard callbacks jika diperlukan
        Log::info("Callback query received: " . $callbackQuery->getData());
        
        Telegram::answerCallbackQuery([
            'callback_query_id' => $callbackQuery->getId(),
            'text' => 'Received!'
        ]);
    }
    
    private function sendMessage($chatId, $text, $parseMode = 'Markdown')
    {
        try {
            return Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending message to {$chatId}: " . $e->getMessage());
            
            // Fallback tanpa markdown jika ada error parsing
            if ($parseMode === 'Markdown') {
                try {
                    return Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => strip_tags(str_replace(['*', '_', '`'], '', $text))
                    ]);
                } catch (\Exception $fallbackError) {
                    Log::error("Fallback message juga gagal: " . $fallbackError->getMessage());
                }
            }
            
            return false;
        }
    }
    
    // Method untuk testing manual
    public function setWebhook()
    {
        try {
            $url = url('/telegram/webhook');
            $response = Telegram::setWebhook(['url' => $url]);
            
            return response()->json([
                'success' => true,
                'webhook_url' => $url,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getWebhookInfo()
    {
        try {
            $info = Telegram::getWebhookInfo();
            
            return response()->json([
                'webhook_url' => $info->getUrl(),
                'pending_updates' => $info->getPendingUpdateCount(),
                'last_error_date' => $info->getLastErrorDate(),
                'last_error_message' => $info->getLastErrorMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

// namespace App\Http\Controllers;

// use App\Models\User;
// use Telegram\Bot\Laravel\Facades\Telegram;
// use Illuminate\Support\Facades\Log;


// class TelegramController extends Controller
// {
//     public function webhook()
// {
//     Log::info('Webhook dipanggil');
    
//     try {
//         $update = Telegram::commandsHandler(true);
        
//         Log::info('Update diterima: ' . json_encode($update));
        
//         // Handle messages
//         if ($update->getMessage()) {
//             $message = $update->getMessage();
//             $chatId = $message->getChat()->getId();
            
//             Log::info('Chat ID diterima: ' . $chatId);
//             $text = $message->getText();
            
//             // Command /start
//             if (strpos($text, '/start') === 0) {
//                 $this->handleStart($chatId, $message);
//             }
            
//             // Command /connect
//             if (strpos($text, '/connect') === 0) {
//                 $this->handleConnect($chatId, $message);
//             }
//         } else {
//             Log::info('Tidak ada message dalam update');
//         }
        
//         return response()->json(['status' => 'success']);
//     } catch (\Exception $e) {
//         Log::error('Error dalam webhook: ' . $e->getMessage());
//         return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
//     }
// }
    
//     private function handleStart($chatId, $message)
//     {
//         $text = "Selamat datang di Bot Absensi Guru!\n\n";
//         $text .= "Untuk menghubungkan akun Anda, kirim perintah:\n";
//         $text .= "/connect [nomor_induk]\n\n";
//         $text .= "Contoh: /connect 198501012010011001";
        
//         Telegram::sendMessage([
//             'chat_id' => $chatId,
//             'text' => $text
//         ]);
//     }
    
//     private function handleConnect($chatId, $message)
// {
//     Log::info("handleConnect dipanggil dengan chatId: $chatId");
    
//     $text = $message->getText();
//     $parts = explode(' ', $text);
    
//     if (count($parts) < 2) {
//         Log::warning("Format salah: $text");
//         Telegram::sendMessage([
//             'chat_id' => $chatId,
//             'text' => 'Format salah. Gunakan: /connect [nomor_induk]'
//         ]);
//         return;
//     }
    
//     $nomorInduk = $parts[1];
//     Log::info("Mencari user dengan nomor_induk: $nomorInduk");
    
//     $user = User::where('nomor_induk', $nomorInduk)->first();
    
//     if (!$user) {
//         Log::warning("User tidak ditemukan dengan nomor_induk: $nomorInduk");
//         Telegram::sendMessage([
//             'chat_id' => $chatId,
//             'text' => 'Nomor induk tidak ditemukan.'
//         ]);
//         return;
//     }
    
//     Log::info("User ditemukan: " . $user->name);
    
//     if ($user->telegram_chat_id) {
//         Log::warning("User sudah memiliki telegram_chat_id: " . $user->telegram_chat_id);
//         Telegram::sendMessage([
//             'chat_id' => $chatId,
//             'text' => 'Akun sudah terhubung dengan Telegram lain.'
//         ]);
//         return;
//     }
    
//     try {
//         $user->telegram_chat_id = $chatId;
//         $result = $user->save();
//         Log::info("Update user result: " . ($result ? 'success' : 'failed'));
        
//         Telegram::sendMessage([
//             'chat_id' => $chatId,
//             'text' => "Berhasil! Akun {$user->name} telah terhubung dengan Telegram."
//         ]);
//     } catch (\Exception $e) {
//         Log::error("Error saat update user: " . $e->getMessage());
//         Telegram::sendMessage([
//             'chat_id' => $chatId,
//             'text' => "Terjadi kesalahan saat menghubungkan akun."
//         ]);
//     }
// }
// }
