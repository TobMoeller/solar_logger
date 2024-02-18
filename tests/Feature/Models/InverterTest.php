<?php

use App\Enums\InverterCommand;
use App\Models\Inverter;
use App\Services\InverterCommander;

it('sends a command to the inverter', function () {
    $inverter = Inverter::factory()
        ->state([
            'ip' => '123.123.123.123',
            'port' => 12345,
        ])
        ->create();

    InverterCommander::shouldReceive('send')
        ->once()
        ->with($inverter, InverterCommand::PDC)
        ->andReturn('foobar');

    $inverter->command(InverterCommand::PDC);
});
