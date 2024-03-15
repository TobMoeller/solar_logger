<?php

use App\Actions\UpdateOrCreateInverterOutput;
use App\Enums\TimespanUnit;
use App\Exceptions\InvalidRecordedAtDate;
use App\Models\Inverter;
use App\Models\InverterOutput;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseHas;

it('creates an inverter output', function (TimespanUnit $timespan, string $dateString) {
    $inverter = Inverter::factory()
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            $timespan,
            $date = Carbon::make($dateString),
            12345
        );

    assertDatabaseHas('inverter_outputs', [
        'inverter_id' => $inverter->id,
        'output' => 12345,
        'timespan' => $timespan,
        'recorded_at' => $date,
    ]);
})->with([
    ['timespan' => TimespanUnit::DAY, 'dateString' => '2024-03-03'],
    ['timespan' => TimespanUnit::MONTH, 'dateString' => '2024-02-01'],
    ['timespan' => TimespanUnit::YEAR, 'dateString' => '2024-01-01'],
]);

it('updates an inverter output', function (TimespanUnit $timespan, string $dateString) {
    $inverter = Inverter::factory()
        ->create();

    $output = InverterOutput::factory()
        ->state([
            'output' => '11111',
            'timespan' => $timespan,
            'recorded_at' => $date = Carbon::make($dateString),
        ])
        ->for($inverter)
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            $timespan,
            $date,
            12345
        );

    expect($output->fresh())
        ->output->toBe(12345);
})->with([
    ['timespan' => TimespanUnit::DAY, 'dateString' => '2024-03-03'],
    ['timespan' => TimespanUnit::MONTH, 'dateString' => '2024-02-01'],
    ['timespan' => TimespanUnit::YEAR, 'dateString' => '2024-01-01'],
]);

it('throws an exception for invalid recorded_at dates', function () {
    $inverter = Inverter::factory()
        ->create();

    (new UpdateOrCreateInverterOutput())
        ->handle(
            $inverter,
            TimespanUnit::YEAR,
            Carbon::make('2024-02-01'),
            12345
        );
})->throws(InvalidRecordedAtDate::class, 'Invalid recorded_at given');
