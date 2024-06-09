<?php

use App\Actions\UpdateOrCreateInverterOutput;
use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverterCommand;
use App\Models\Inverter;
use App\Models\InverterOutput;

use function Pest\Laravel\assertDatabaseHas;

it('creates an inverter output', function (InverterCommand $command) {
    $inverter = Inverter::factory()
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            $command,
            12345
        );

    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 12345,
        'timespan' => $command->getOutputTimespan(),
        'recorded_at' => $command->getOutputDate(),
    ]);
})->with(InverterCommand::outputCommands());

it('updates an inverter output', function (InverterCommand $command) {
    $inverter = Inverter::factory()
        ->create();

    $output = InverterOutput::factory()
        ->state([
            'output' => '11111',
            'timespan' => $command->getOutputTimespan(),
            'recorded_at' => $command->getOutputDate(),
        ])
        ->for($inverter)
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            $command,
            12345
        );

    expect($output->fresh())
        ->output->toBe(12345);
})->with(InverterCommand::outputCommands());

it('throws an exception for invalid recorded_at dates', function () {
    $inverter = Inverter::factory()
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            InverterCommand::UDC,
            12345
        );
})->throws(InvalidInverterCommand::class, 'Invalid command');

it('does not update an inverter output with invalid data', function (InverterCommand $command) {
    $inverter = Inverter::factory()
        ->create();

    $output = InverterOutput::factory()
        ->state([
            'output' => '11111',
            'timespan' => $command->getOutputTimespan(),
            'recorded_at' => $command->getOutputDate(),
        ])
        ->for($inverter)
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            $command,
            0
        );

    expect($output->fresh())
        ->output->toBe(11111);
})->with(InverterCommand::outputCommands());
