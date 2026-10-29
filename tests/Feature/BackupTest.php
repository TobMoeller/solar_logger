<?php

use Illuminate\Support\Facades\Config;
use Spatie\Backup\Enums\Encryption;

it('has a backup enabled config', function () {
    expect(Config::get('backup.enabled'))
        ->toBeFalse();
});

it('loads the upgraded backup configuration without a notification recipient', function () {
    Config::set('backup.notifications.mail.to', []);

    $backup = Spatie\Backup\Config\Config::fromArray(config('backup'));

    expect($backup->backup->destination->disks)->toBe(['backup'])
        ->and($backup->backup->encryption)->toBe(Encryption::None)
        ->and($backup->notifications->mail->to)->toBe([]);
});
