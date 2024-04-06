<?php

namespace App\Services\InverterCommander;

use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverter;
use App\Exceptions\InverterUnreachable;
use App\Models\Inverter;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;

class PythonScript implements InverterCommanderContract
{
    /**
     * @throws InvalidInverter
     * @throws ProcessFailedException
     * @throws InverterUnreachable
     */
    public function send(Inverter $inverter, InverterCommand $command): mixed
    {
        throw_if(empty($inverter->ip) || empty($inverter->port), InvalidInverter::class, 'Missing IP address or port');

        $process = Process::run([
            'python',
            config('inverter.command.python_script_path'),
            $inverter->ip,
            $inverter->port,
            $command->value,
        ]);

        throw_if(
            $process->failed() ||
            ! is_array($response = json_decode($process->output(), true)) ||
            ! Arr::has($response, ['success', 'data', 'error']),
            ProcessFailedException::class,
            $process
        );

        throw_if($response['success'] === false, InverterUnreachable::class, $response['error']);

        return $command->transformationStrategy()->transform($response['data']);
    }
}
