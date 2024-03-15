<?php

namespace App\Actions;

use App\Enums\TimespanUnit;
use App\Exceptions\InvalidRecordedAtDate;
use App\Models\Inverter;
use App\Models\InverterOutput;
use Illuminate\Support\Carbon;

class UpdateOrCreateInverterOutput
{
    public function handle(Inverter $inverter, TimespanUnit $timespan, Carbon $recordedAt, mixed $output): InverterOutput
    {
        throw_unless(
            $timespan->isValidRecordedAtDate($recordedAt),
            InvalidRecordedAtDate::class,
            'Invalid recorded_at given'
        );

        return $inverter->outputs()
            ->updateOrCreate(
                [
                    'timespan' => $timespan,
                    'recorded_at' => $recordedAt,
                ],
                [
                    'output' => $output,
                ]
            );
    }
}
