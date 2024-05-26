<?php

use Illuminate\Support\Facades\Config;

it('has a backup enabled config', function () {
    expect(Config::get('backup.enabled'))
        ->toBeFalse();
});
