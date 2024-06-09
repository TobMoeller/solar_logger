<?php

use App\Enums\InverterCommand;
use App\Enums\TimespanUnit;
use App\Exceptions\InverterUnreachable;
use App\Models\Inverter;
use App\Models\InverterOutput;
use App\Models\InverterStatus;
use App\Services\InverterCommander;
use App\Services\InverterMonitor;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseHas;

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

it('creates inverter outputs', function () {
    Carbon::setTestNow(now());
    $inverter = Inverter::factory()
        ->has(InverterStatus::factory(['is_online' => true]), 'statuses')
        ->create();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_TODAY)
        ->once()
        ->andReturn('1111');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YESTERDAY)
        ->once()
        ->andReturn('2222');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_MONTH)
        ->once()
        ->andReturn('3333');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YEAR)
        ->once()
        ->andReturn('4444');

    (new InverterMonitor($inverter))->updateOutput();

    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 1111,
        'timespan' => TimespanUnit::DAY,
        'recorded_at' => now()->startOfDay(),
    ]);
    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 2222,
        'timespan' => TimespanUnit::DAY,
        'recorded_at' => now()->yesterday(),
    ]);
    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 3333,
        'timespan' => TimespanUnit::MONTH,
        'recorded_at' => now()->startOfMonth(),
    ]);
    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 4444,
        'timespan' => TimespanUnit::YEAR,
        'recorded_at' => now()->startOfYear(),
    ]);

    Carbon::setTestNow();
});

it('updates inverter outputs', function () {
    Carbon::setTestNow(now());
    $inverter = Inverter::factory()
        ->has(InverterStatus::factory(['is_online' => true]), 'statuses')
        ->create();
    $outputs = InverterOutput::factory()
        ->for($inverter)
        ->state(new Sequence(
            [
                'timespan' => TimespanUnit::DAY,
                'output' => 999,
                'recorded_at' => now()->startOfDay(),
                'updated_at' => now(),
            ],
            [
                'timespan' => TimespanUnit::DAY,
                'output' => 999,
                'recorded_at' => now()->yesterday(),
                'updated_at' => now()->yesterday(),
            ],
            [
                'timespan' => TimespanUnit::MONTH,
                'output' => 999,
                'recorded_at' => now()->startOfMonth(),
                'updated_at' => now()->yesterday(),
            ],
            [
                'timespan' => TimespanUnit::YEAR,
                'output' => 999,
                'recorded_at' => now()->startOfYear(),
                'updated_at' => now()->yesterday(),
            ],
        ))
        ->count(4)
        ->create();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_TODAY)
        ->once()
        ->andReturn('1111');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YESTERDAY)
        ->once()
        ->andReturn('2222');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_MONTH)
        ->once()
        ->andReturn('3333');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YEAR)
        ->once()
        ->andReturn('4444');

    (new InverterMonitor($inverter))->updateOutput();

    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 1111,
        'timespan' => TimespanUnit::DAY,
        'recorded_at' => now()->startOfDay(),
        'updated_at' => now(),
    ]);
    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 2222,
        'timespan' => TimespanUnit::DAY,
        'recorded_at' => now()->yesterday(),
        'updated_at' => now(),
    ]);
    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 3333,
        'timespan' => TimespanUnit::MONTH,
        'recorded_at' => now()->startOfMonth(),
        'updated_at' => now(),
    ]);
    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 4444,
        'timespan' => TimespanUnit::YEAR,
        'recorded_at' => now()->startOfYear(),
        'updated_at' => now(),
    ]);

    expect($inverter->outputs()->count())
        ->toBe(4);

    Carbon::setTestNow();
});

it('updates only the output of the current day if ealier outputs have already been updated today', function () {
    Carbon::setTestNow(now());
    $inverter = Inverter::factory()
        ->has(InverterStatus::factory(['is_online' => true]), 'statuses')
        ->create();
    $outputs = InverterOutput::factory()
        ->for($inverter)
        ->state(new Sequence(
            [
                'timespan' => TimespanUnit::DAY,
                'output' => 999,
                'recorded_at' => now()->startOfDay(),
                'updated_at' => now(),
            ],
            [
                'timespan' => TimespanUnit::DAY,
                'output' => 999,
                'recorded_at' => now()->yesterday(),
                'updated_at' => now(),
            ],
            [
                'timespan' => TimespanUnit::MONTH,
                'output' => 999,
                'recorded_at' => now()->startOfMonth(),
                'updated_at' => now(),
            ],
            [
                'timespan' => TimespanUnit::YEAR,
                'output' => 999,
                'recorded_at' => now()->startOfYear(),
                'updated_at' => now(),
            ],
        ))
        ->count(4)
        ->create();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_TODAY)
        ->once()
        ->andReturn('1111');
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YESTERDAY)
        ->never();
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_MONTH)
        ->never();
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YEAR)
        ->never();

    (new InverterMonitor($inverter))->updateOutput();

    expect($outputs->fresh()->pluck('output'))
        ->toMatchArray([
            1111,
            999,
            999,
            999,
        ]);

    Carbon::setTestNow();
});

it('creates/updates no outputs if the inverter is offline', function () {
    $inverter = Inverter::factory()
        ->has(InverterStatus::factory(['is_online' => false]), 'statuses')
        ->create();

    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_TODAY)
        ->never();
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YESTERDAY)
        ->never();
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_MONTH)
        ->never();
    InverterCommander::shouldReceive('send')
        ->with($inverter, InverterCommand::YIELD_YEAR)
        ->never();

    (new InverterMonitor($inverter))->updateOutput();
});
