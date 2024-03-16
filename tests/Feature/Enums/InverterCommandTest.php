<?php

use App\Enums\InverterCommand;
use App\Enums\TimespanUnit;

it('returns output commands', function () {
    expect(InverterCommand::outputCommands())
        ->toMatchArray([
            InverterCommand::YIELD_TODAY,
            InverterCommand::YIELD_YESTERDAY,
            InverterCommand::YIELD_MONTH,
            InverterCommand::YIELD_YEAR,
        ]);
});

it('returns true if it is an output command', function (InverterCommand $command) {
    expect($command->isOutputCommand())
        ->toBeTrue();
})->with(InverterCommand::outputCommands());

it('returns the corresponding timespan unit', function (InverterCommand $command, TimespanUnit $timespan) {
    expect($command->getOutputTimespan())
        ->toBe($timespan);
})->with([
    [InverterCommand::YIELD_TODAY, TimespanUnit::DAY],
    [InverterCommand::YIELD_YESTERDAY, TimespanUnit::DAY],
    [InverterCommand::YIELD_MONTH, TimespanUnit::MONTH],
    [InverterCommand::YIELD_YEAR, TimespanUnit::YEAR],
]);

it('returns a valid recorded at date', function (InverterCommand $command) {
    $timespan = $command->getOutputTimespan();
    expect($timespan->isValidRecordedAtDate($command->getOutputDate()))
        ->toBeTrue();
})->with(InverterCommand::outputCommands());
