<?php

use Illuminate\Bus\PendingBatch;
use Illuminate\Cache\NoLock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Modules\Export\Actions\DispatchExportJobs;
use Modules\Export\Jobs\ClearExportLock;
use Modules\Export\Jobs\ExportExportable;
use Modules\Export\Models\ExportEntry;

it('dispatches export jobs for updated and new export entries', function () {
    Bus::fake();

    Cache::partialMock()
        ->shouldReceive('exportLock')
        ->once()
        ->andReturn(new NoLock('testitest', 600, '::owner::'));

    Carbon::setTestNow(now());

    $shouldGetExported = [];

    foreach (ExportEntry::exportables() as $exportableClass) {
        $shouldGetExported[] = $exportableClass::factory()
            ->state(['updated_at' => now()])
            ->has(
                ExportEntry::factory()
                    ->state(new Sequence(
                        [
                            'server_id' => null,
                            'exported_at' => now()->addMinute(),
                        ],
                        [
                            'server_id' => fake()->randomNumber(),
                            'exported_at' => now()->subMinute(),
                        ],
                    )),
                'exportEntry'
            )
            ->count(4)
            ->create();

        $exportableClass::factory()
            ->state(['updated_at' => now()])
            ->has(
                ExportEntry::factory()
                    ->state([
                        'server_id' => fake()->randomNumber(),
                        'exported_at' => now()->addMinute(),
                    ]),
                'exportEntry'
            )
            ->create();
    }

    (new DispatchExportJobs())->handle();

    Bus::assertChained([
        Bus::chainedBatch(function (PendingBatch $batch) use ($shouldGetExported) {
            return $batch->jobs->count() === 4 &&
                $batch->jobs->reduce(function (bool $carry, ShouldQueue $job) use ($shouldGetExported) {
                    return $carry &&
                        $job instanceof ExportExportable &&
                        $job->exportable::class === ExportEntry::exportables()[0] &&
                        in_array($job->exportable->id, $shouldGetExported[0]->pluck('id')->toArray());
                }, true);
        }),
        Bus::chainedBatch(function (PendingBatch $batch) use ($shouldGetExported) {
            return $batch->jobs->count() === 4 &&
                $batch->jobs->reduce(function (bool $carry, ShouldQueue $job) use ($shouldGetExported) {
                    return $carry &&
                        $job instanceof ExportExportable &&
                        $job->exportable::class === ExportEntry::exportables()[1] &&
                        in_array($job->exportable->id, $shouldGetExported[1]->pluck('id')->toArray());
                }, true);
        }),
        Bus::chainedBatch(function (PendingBatch $batch) use ($shouldGetExported) {
            return $batch->jobs->count() === 4 &&
                $batch->jobs->reduce(function (bool $carry, ShouldQueue $job) use ($shouldGetExported) {
                    return $carry &&
                        $job instanceof ExportExportable &&
                        $job->exportable::class === ExportEntry::exportables()[2] &&
                        in_array($job->exportable->id, $shouldGetExported[2]->pluck('id')->toArray());
                }, true);
        }),
        new ClearExportLock('::owner::'),
    ]);

    Carbon::setTestNow();
});
