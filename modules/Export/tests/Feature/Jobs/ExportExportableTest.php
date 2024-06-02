<?php

use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Modules\Export\Actions\ExportToServer;
use Modules\Export\Jobs\ExportExportable;
use Modules\Export\Models\ExportEntry;

it('exports an exportable', function (string $exportableClass) {
    $exportable = $exportableClass::factory()->create();

    $mock = Mockery::mock(ExportToServer::class, function (MockInterface $mock) use ($exportable) {
        $mock->shouldReceive('handle')
            ->with(Mockery::on(fn ($arg) => $arg->is($exportable)))
            ->once();
    });
    App::bind(ExportToServer::class, fn () => $mock);

    ExportExportable::dispatch($exportable);
})->with(ExportEntry::exportables());
