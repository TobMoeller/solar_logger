<?php

use App\Enums\InverterCommand;
use App\Exceptions\InverterUnreachable;
use App\Models\Inverter;
use App\Models\InverterStatus;
use App\Services\InverterCommander;
use App\Services\InverterMonitor;

it('creates a new inverter status with online status true', function () {
    $inverter = Inverter::factory()->create();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::UDC)
        ->once()
        ->andReturn('1.123456e+02');

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::IDC)
        ->once()
        ->andReturn('2.123456e+00');

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::PAC)
        ->once()
        ->andReturn('3.123456e+03');

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::PDC)
        ->once()
        ->andReturn('4.123456e+03');

    (new InverterMonitor($inverter))->updateStatus();

    expect($inverter->statuses()->first())
        ->toBeInstanceOf(InverterStatus::class)
        ->is_online->toBeTrue()
        ->udc->toBe(112.3456)
        ->idc->toBe(2.123456)
        ->pac->toBe(3123.456)
        ->pdc->toBe(4123.456);
});

it('creates a new inverter status with online status false', function () {
    $inverter = Inverter::factory()->create();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::UDC)
        ->once()
        ->andThrow(InverterUnreachable::class);

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::IDC)
        ->never();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::PAC)
        ->never();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::PDC)
        ->never();

    (new InverterMonitor($inverter))->updateStatus();

    expect($inverter->statuses()->first())
        ->toBeInstanceOf(InverterStatus::class)
        ->is_online->toBeFalse()
        ->udc->toBeNull()
        ->idc->toBeNull()
        ->pac->toBeNull()
        ->pdc->toBeNull();
});
