<?php

namespace App\Jobs;

use App\Models\Inverter;
use App\Services\InverterMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MonitorInverters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Inverter::where('is_monitored', true)
            ->get()
            ->each(function (Inverter $inverter) {
                app(InverterMonitor::class, ['inverter' => $inverter])
                    ->updateStatus()
                    ->updateOutput();
            });
    }
}
