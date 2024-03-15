<?php

use App\Enums\TimespanUnit;
use Illuminate\Support\Carbon;

it('returns true if a date can be used as a YEAR timespan recorded at date', function () {
    $date = Carbon::make('2024-01-01');

    expect(TimespanUnit::YEAR->isValidRecordedAtDate($date))
        ->toBeTrue();
});

it('returns true if a date can be used as a MONTH timespan recorded at date', function () {
    $date = Carbon::make('2024-02-01');

    expect(TimespanUnit::MONTH->isValidRecordedAtDate($date))
        ->toBeTrue();
});

it('returns true if a date can be used as a DAY timespan recorded at date', function () {
    $date = Carbon::make('2024-02-09');

    expect(TimespanUnit::DAY->isValidRecordedAtDate($date))
        ->toBeTrue();
});

it('returns false if a date can not be used as a YEAR timespan recorded at date', function (string $dateString) {
    $date = Carbon::make($dateString);

    expect(TimespanUnit::YEAR->isValidRecordedAtDate($date))
        ->toBeFalse();
})->with([
    '2024-01-01 12:34',
    '2024-01-02',
]);

it('returns false if a date can not be used as a MONTH timespan recorded at date', function (string $dateString) {
    $date = Carbon::make($dateString);

    expect(TimespanUnit::MONTH->isValidRecordedAtDate($date))
        ->toBeFalse();
})->with([
    '2024-02-01 12:34',
    '2024-02-02',
]);

it('returns false if a date can not be used as a DAY timespan recorded at date', function () {
    $date = Carbon::make('2024-01-01 12:34');

    expect(TimespanUnit::DAY->isValidRecordedAtDate($date))
        ->toBeFalse();
});
