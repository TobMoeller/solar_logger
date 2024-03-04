<?php

use App\Jobs\MonitorInverters;
use App\Models\Inverter;
use App\Services\InverterMonitor;
use Illuminate\Foundation\Application;
use Mockery\MockInterface;

test('it updates every inverter that should be monitored', function () {
    [$inverter1, $inverter2] = Inverter::factory(['is_monitored' => true])
        ->count(2)
        ->create();
    $notMonitoredInverter = Inverter::factory(['is_monitored' => false])
        ->create();

    $mock1 = Mockery::mock(InverterMonitor::class, [$inverter1], function (MockInterface $mock) {
        $mock->shouldReceive('updateStatus')
            ->once();
    });
    $mock2 = Mockery::mock(InverterMonitor::class, [$inverter2], function (MockInterface $mock) {
        $mock->shouldReceive('updateStatus')
            ->once();
    });
    $notMonitoredMock = Mockery::mock(InverterMonitor::class, [$notMonitoredInverter], function (MockInterface $mock) {
        $mock->shouldReceive('updateStatus')
            ->never();
    });
    app()->bind(
        InverterMonitor::class,
        fn (Application $app, array $args) => match ($args['inverter']->id) {
            $inverter1->id => $mock1,
            $inverter2->id => $mock2,
            $notMonitoredInverter->id => $notMonitoredMock,
        });

    (new MonitorInverters())->handle();
});
