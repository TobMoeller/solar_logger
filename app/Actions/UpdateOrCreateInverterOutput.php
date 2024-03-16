<?php

namespace App\Actions;

use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverterCommand;
use App\Models\Inverter;
use App\Models\InverterOutput;

class UpdateOrCreateInverterOutput
{
    public function handle(Inverter $inverter, InverterCommand $command, mixed $output): InverterOutput
    {
        throw_unless(
            $command->isOutputCommand(),
            InvalidInverterCommand::class,
            'Invalid command'
        );

        return $inverter->outputs()
            ->updateOrCreate(
                [
                    'timespan' => $command->getOutputTimespan(),
                    'recorded_at' => $command->getOutputDate(),
                ],
                [
                    'output' => $output,
                ]
            );
    }
}
