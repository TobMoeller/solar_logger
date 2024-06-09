<?php

namespace App\Actions;

use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverterCommand;
use App\Models\Inverter;
use App\Models\InverterOutput;
use Illuminate\Support\Facades\Log;

class UpdateOrCreateInverterOutput
{
    public function handle(Inverter $inverter, InverterCommand $command, mixed $output): InverterOutput
    {
        throw_unless(
            $command->isOutputCommand(),
            InvalidInverterCommand::class,
            'Invalid command'
        );

        $inverterOutput = $inverter->outputs()
            ->firstOrCreate(
                [
                    'timespan' => $command->getOutputTimespan(),
                    'recorded_at' => $command->getOutputDate(),
                ],
                [
                    'output' => $output,
                ]
            );

        if ($output < $inverterOutput->output) {
            Log::error(self::class.': Trying to update output with invalid value', [
                'inverter' => $inverter->id,
                'inverterOutput' => $inverterOutput,
                'command' => $command->value,
                'output' => $output,
            ]);
        } else {
            $inverterOutput->output = $output;
            $inverterOutput->saveOrFail();
        }

        return $inverterOutput;
    }
}
