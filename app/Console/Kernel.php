<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {

        // Kirim notifikasi pengingat 15 menit sebelum jadwal (setiap 5 menit)
        $schedule->command('notifikasi:jadwal')
                 ->everyFiveMinutes()
                 ->weekdays()
                 ->between('06:00', '18:00')
                 ->withoutOverlapping();
        
        // Kirim notifikasi ketika jadwal dimulai (setiap menit)
        $schedule->command('notifikasi:jadwal-dimulai')
                 ->everyMinute()
                 ->weekdays()
                 ->between('06:00', '18:00')
                 ->withoutOverlapping();
        
        // Kirim notifikasi guru terlambat (setiap 3 menit)
        $schedule->command('notifikasi:guru-terlambat')
                 ->everyThreeMinutes()
                 ->weekdays()
                 ->between('06:00', '18:00')
                 ->withoutOverlapping();
        
        // Process auto alfa setiap 30 menit selama jam sekolah
        $schedule->command('absensi:process-auto-alfa')
                 ->everyThirtyMinutes()
                 ->weekdays()
                 ->between('07:00', '17:00')
                 ->withoutOverlapping();
        
        // Kirim laporan harian jam 17:00 (hari kerja saja)
        $schedule->command('laporan:harian')
                 ->weekdays()
                 ->dailyAt('17:00')
                 ->withoutOverlapping();
        
        // Backup dan maintenance tengah malam
        $schedule->command('backup:clean')
                 ->daily()
                 ->at('01:00');
                 
        $schedule->command('queue:restart')
                 ->daily()
                 ->at('02:00');
        
        // Optional: Test command untuk debugging (hanya di development)
        if (app()->environment('local')) {
            $schedule->command('notifikasi:jadwal')
                     ->everyMinute()
                     ->appendOutputTo(storage_path('logs/scheduler.log'));
        }

        
    //    // Kirim notifikasi jadwal 15 menit sebelum dimulai (setiap 5 menit)
    //     $schedule->command('notifikasi:jadwal')->everyFiveMinutes();
        
    //     // Kirim notifikasi ketika jadwal dimulai (setiap menit)
    //     $schedule->command('notifikasi:jadwal-dimulai')->everyMinute();
        
    //     // Kirim notifikasi guru terlambat (setiap 5 menit)
    //     $schedule->command('notifikasi:guru-terlambat')->everyFiveMinutes();
        
    //     // Process auto alfa setiap jam selama jam sekolah
    //     $schedule->command('absensi:process-auto-alfa')
    //              ->hourlyAt(5)
    //              ->weekdays()
    //              ->between('07:00', '17:00');
        
    //     // Kirim laporan harian jam 17:00
    //     $schedule->command('laporan:harian')->dailyAt('17:00');
        
    //     // Backup dan maintenance tengah malam
    //     $schedule->command('backup:clean')->daily()->at('01:00');
    //     $schedule->command('queue:restart')->daily()->at('02:00');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
