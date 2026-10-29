<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Exercise the real middleware; Laravel bypasses CSRF checks in testing.
    $this->app['env'] = 'local';
    Route::middleware('web')->post('/upgrade-csrf-test', fn () => response('ok'));
});

it('rejects a post without a csrf token', function () {
    $this->post('/upgrade-csrf-test')->assertStatus(419);
});

it('accepts a post with a matching csrf token', function () {
    $this->withSession(['_token' => 'test-token'])
        ->post('/upgrade-csrf-test', ['_token' => 'test-token'])
        ->assertOk();
});
