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

        $schedule->command('cashflows:sync-is-repeated')
            ->monthlyOn(1, '00:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('Currency is sync command failed');
            });

        $schedule->command('storage:clean-images')
            ->weeklyOn('1', '02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('Image cleaning command failed');
            });

        $schedule->command('contacts:update-next-call')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('Contacts next call update command failed');
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
