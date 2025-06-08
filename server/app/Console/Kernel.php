<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('email:send-queue')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('Email queue command failed');
            });

        $schedule->command('currency:sync-rates')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('Currency sync command failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
