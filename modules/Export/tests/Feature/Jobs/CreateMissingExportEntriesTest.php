<?php

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Modules\Export\Actions\GenerateExportEntries;
use Modules\Export\Jobs\CreateMissingExportEntries;

it('is unique', function () {
    expect(CreateMissingExportEntries::class)->toImplement(ShouldBeUnique::class);
});

it('runs the generate export entries action', function () {
    $mock = Mockery::mock(GenerateExportEntries::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')
            ->once();
    });
    App::bind(GenerateExportEntries::class, fn () => $mock);

    CreateMissingExportEntries::dispatch();
});
