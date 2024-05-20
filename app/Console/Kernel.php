<?php

namespace App\Console;

use App\Jobs\MonitorInverters;
use App\Jobs\NotifyForOfflineInverters;
use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\Export\Actions\DispatchExportJobs;
use Modules\Export\Jobs\CreateMissingExportEntries;

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

        $schedule->job(NotifyForOfflineInverters::class)
            ->when(config('inverter.notifications.enabled'))
            ->dailyAt('10:00');

        if (config('backup.enabled')) {
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');
        }

        $schedule->job(CreateMissingExportEntries::class)
            ->when(config('export.create_entries.enabled'))
            ->everyFiveMinutes()
            ->between('04:00', '22:00');

        $schedule->call(fn () => (new DispatchExportJobs())->handle())
            ->when(config('export.export_to_server.enabled'))
            ->everyFiveMinutes()
            ->between('04:00', '22:00');

        $schedule->command('queue:prune-batches')
            ->daily();
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
