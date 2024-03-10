<?php

use App\Enums\InverterCommand;
use App\Models\Inverter;
use App\Models\InverterStatus;
use App\Services\InverterCommander;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;

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

it('has a latestStatus relationship', function () {
    $inverter = Inverter::factory()
        ->has(
            InverterStatus::factory()
                ->state(new Sequence(
                    ['id' => 1, 'created_at' => now()->subDays(2)],
                    ['id' => 2, 'created_at' => now()->subDays(1)]
                ))
                ->count(2),
            'statuses'
        )
        ->create();

    expect($inverter->latestStatus)
        ->id->toBe(2);
});

it('has is_online trait', function (bool $isOnline) {
    Carbon::setTestNow(now());

    $inverter = Inverter::factory()
        ->has(
            InverterStatus::factory()
                ->state([
                    'is_online' => true,
                    'created_at' => $isOnline ? now()->subMinutes(29) : now()->subMinutes(31)
                ]),
            'statuses'
        )
        ->create();

    expect($inverter->is_online)
        ->toBe($isOnline);

    Carbon::setTestNow();
})->with([true, false]);
