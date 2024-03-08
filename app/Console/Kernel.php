<?php

namespace App\Console;

use App\Jobs\MonitorInverters;
use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(MonitorInverters::class)
            ->when(config('inverter.monitor.enabled'))
            ->everyFiveMinutes()
            ->between('04:00', '22:00');
    }

    protected function scheduleTimezone(): DateTimeZone|string|null
    {
        return 'Europe/Berlin';
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
