<?php

use Illuminate\Validation\ValidationException;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

it('creates an inverter', function () {
    artisan('app:create-inverter')
        ->expectsQuestion('IP Address', '123.123.123.123')
        ->expectsQuestion('Port', '12345')
        ->assertOk();

    assertDatabaseHas('inverters', [
        'ip' => '123.123.123.123',
        'port' => '12345',
    ]);
});

it('throws a validation exception for invalid input', function (string $ip, int $port) {
    artisan('app:create-inverter')
        ->expectsQuestion('IP Address', $ip)
        ->expectsQuestion('Port', $port)
        ->assertFailed();

})->with([
    'invalid ip' => ['ip' => '999.999.999.999', 'port' => 12345],
    'invlid port' => ['ip' => '123.123.123.123', 'port' => 99999],
])->throws(ValidationException::class);
