<?php

use App\Models\InverterStatus;
use Illuminate\Support\Carbon;

use function Pest\Laravel\artisan;

it('updates the updated_at column for all inverter statuses', function () {
    Carbon::setTestNow(now());

    InverterStatus::factory(['updated_at' => now()->subDay()])->count(10)->create();

    artisan('app:mark-all-status-for-export')
        ->expectsConfirmation('Do you really want to trigger an export for all inverter statuses?', 'yes')
        ->expectsOutput('All inverter statuses were updated')
        ->assertOk();

    expect(InverterStatus::whereDate('updated_at', now())->count())
        ->toBe(10);
});

it('does not updates the updated_at column for all inverter statuses if not confirmed', function () {
    Carbon::setTestNow(now());

    InverterStatus::factory(['updated_at' => now()->subDay()])->count(10)->create();

    artisan('app:mark-all-status-for-export')
        ->expectsConfirmation('Do you really want to trigger an export for all inverter statuses?')
        ->expectsOutput('Cancelled')
        ->assertOk();

    expect(InverterStatus::whereDate('updated_at', now())->count())
        ->toBe(0);
});
