<?php

namespace App\Console;

use App\Console\Commands\DeleteCommand;
use App\Console\Commands\ImportCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command(ImportCommand::class)->monthly();
        $schedule->command(DeleteCommand::class)->monthlyOn(2);
        $schedule->command('sitemap')->monthlyOn(3);
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
