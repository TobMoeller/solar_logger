<?php

use App\Jobs\NotifyForOfflineInverters;
use App\Models\Inverter;
use App\Models\InverterStatus;
use App\Notifications\InverterIsOfflineNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

it('sends notifications for offline inverters', function () {
    Notification::fake();
    Carbon::setTestNow(now());
    Config::set('inverter.notifications.email', 'foo@bar.de');

    $inverters = Inverter::factory(['is_monitored' => true])
        ->has(
            InverterStatus::factory()
                ->state([
                    'is_online' => true,
                    'created_at' => now()->subDays(2),
                ]),
            'statuses'
        )
        ->count(2)
        ->create();

    (new NotifyForOfflineInverters())->handle();

    Notification::assertSentOnDemandTimes(InverterIsOfflineNotification::class, 2);
    Notification::assertSentOnDemand(InverterIsOfflineNotification::class, function (InverterIsOfflineNotification $notification) use ($inverters) {
        return $notification->inverter->is($inverters->get(0));
    });
    Notification::assertSentOnDemand(InverterIsOfflineNotification::class, function (InverterIsOfflineNotification $notification) use ($inverters) {
        return $notification->inverter->is($inverters->get(1));
    });

    Carbon::setTestNow();
});

it('throws an exception if no email is provided', function () {
    (new NotifyForOfflineInverters())->handle();
})->throws(Exception::class, 'No email provided');

it('does not send notifications for online inverters', function () {
    Notification::fake();
    Config::set('inverter.notifications.email', 'foo@bar.de');

    Inverter::factory(['is_monitored' => true])
        ->has(
            InverterStatus::factory()
                ->state([
                    'is_online' => true,
                    'created_at' => now(),
                ]),
            'statuses'
        )
        ->create();

    (new NotifyForOfflineInverters())->handle();

    Notification::assertNothingSent();
});
