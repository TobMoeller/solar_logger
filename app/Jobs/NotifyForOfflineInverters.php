<?php

namespace App\Jobs;

use App\Models\Inverter;
use App\Notifications\InverterIsOfflineNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NotifyForOfflineInverters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        throw_unless(
            $email = config('inverter.notifications.email'),
            Exception::class,
            'No email provided'
        );

        Inverter::where('is_monitored', true)
            ->isOfflineForOneDay()
            ->each(function (Inverter $inverter) use ($email) {
                Notification::route('mail', $email)
                    ->notify(new InverterIsOfflineNotification($inverter));
            });
    }
}
