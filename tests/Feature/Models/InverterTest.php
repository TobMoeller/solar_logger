<?php

use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverterCommand;
use App\Models\Inverter;
use App\Models\InverterOutput;
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
                    'created_at' => $isOnline ? now()->subMinutes(29) : now()->subMinutes(31),
                ]),
            'statuses'
        )
        ->create();

    expect($inverter->is_online)
        ->toBe($isOnline);

    Carbon::setTestNow();
})->with([true, false]);

it('returns if the output for a certain timespan was updated today', function (InverterCommand $command, bool $updatedToday) {
    $inverter = Inverter::factory()
        ->create();

    InverterOutput::factory()
        ->state([
            'timespan' => $command->getOutputTimespan(),
            'recorded_at' => $command->getOutputDate(),
            'updated_at' => $updatedToday ? now() : now()->subDay(),
        ])
        ->for($inverter)
        ->create();

    expect($inverter->outputWasUpdatedToday($command))
        ->toBe($updatedToday);
})->with(InverterCommand::outputCommands(), [true, false]);

it('throws an exception for invalid recorded_at dates', function () {
    $inverter = Inverter::factory()
        ->create();

    $inverter->outputWasUpdatedToday(InverterCommand::UDC);
})->throws(InvalidInverterCommand::class, 'Invalid command');

it('queries for inverters, without an online status within the last day', function () {
    Carbon::setTestNow(now());

    $notFoundInverter = Inverter::factory()
        ->has(
            InverterStatus::factory()
                ->state([
                    'is_online' => true,
                    'created_at' => now(),
                ]),
            'statuses'
        )
        ->create();
    $inverter1 = Inverter::factory()
        ->has(
            InverterStatus::factory()
                ->state([
                    'is_online' => true,
                    'created_at' => now()->subDay()->subHour(),
                ]),
            'statuses'
        )
        ->create();
    $inverter2 = Inverter::factory()
        ->has(
            InverterStatus::factory()
                ->state([
                    'is_online' => false,
                    'created_at' => now(),
                ]),
            'statuses'
        )
        ->create();

    expect(Inverter::isOfflineForOneDay()->get())
        ->pluck('id')
        ->toContain($inverter1->id, $inverter2->id)
        ->not()->toContain($notFoundInverter->id);

    Carbon::setTestNow();
});
