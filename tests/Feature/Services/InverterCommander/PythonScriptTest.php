<?php

use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverter;
use App\Exceptions\InverterUnreachable;
use App\Models\Inverter;
use App\Services\InverterCommander\PythonScript;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

it('calls the python script with command data', function () {
    Process::fake([
        '*' => Process::result(json_encode([
            'success' => true,
            'data' => '123',
            'error' => null,
        ])),
    ]);

    $inverter = Inverter::factory()
        ->state([
            'ip' => '123.123.123.123',
            'port' => 12345,
        ])
        ->create();

    (new PythonScript())->send($inverter, InverterCommand::UDC);

    Process::assertRan(function (PendingProcess $process) use ($inverter) {
        expect($process->command)
            ->toMatchArray([
                'python',
                config('inverter.command.python_script_path'),
                $inverter->ip,
                $inverter->port,
                InverterCommand::UDC->value,
            ]);

        return true;
    });
});

it('throws an invalid inverter exception', function (?string $ip, ?int $port) {
    Process::fake();

    $inverter = Inverter::factory()
        ->state([
            'ip' => $ip,
            'port' => $port,
        ])
        ->create();

    expect(fn () => (new PythonScript())->send($inverter, InverterCommand::UDC))
        ->toThrow(InvalidInverter::class, 'Missing IP address or port');

    Process::assertNothingRan();
})->with([
    'missing ip' => ['ip' => '123.123.123.123', 'port' => null],
    'missing port' => ['ip' => null, 'port' => 12345],
]);

it('throws process failed exception', function () {
    Process::fake([
        '*' => Process::result('foobar'),
    ]);

    $inverter = Inverter::factory()
        ->state([
            'ip' => '123.123.123.123',
            'port' => 12345,
        ])
        ->create();

    expect(fn () => (new PythonScript())->send($inverter, InverterCommand::UDC))
        ->toThrow(ProcessFailedException::class);

    Process::assertRan(function (PendingProcess $process) use ($inverter) {
        expect($process->command)
            ->toMatchArray([
                'python',
                config('inverter.command.python_script_path'),
                $inverter->ip,
                $inverter->port,
                InverterCommand::UDC->value,
            ]);

        return true;
    });
});

it('throws a inverter unreachable exception', function () {
    Process::fake([
        '*' => Process::result(json_encode([
            'success' => false,
            'data' => null,
            'error' => 'foobar',
        ])),
    ]);

    $inverter = Inverter::factory()
        ->state([
            'ip' => '123.123.123.123',
            'port' => 12345,
        ])
        ->create();

    expect(fn () => (new PythonScript())->send($inverter, InverterCommand::UDC))
        ->toThrow(InverterUnreachable::class);

    Process::assertRan(function (PendingProcess $process) use ($inverter) {
        expect($process->command)
            ->toMatchArray([
                'python',
                config('inverter.command.python_script_path'),
                $inverter->ip,
                $inverter->port,
                InverterCommand::UDC->value,
            ]);

        return true;
    });
});
