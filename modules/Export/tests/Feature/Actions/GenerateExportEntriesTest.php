<?php

use Modules\Export\Actions\GenerateExportEntries;
use Modules\Export\Contracts\Exportable;
use Modules\Export\Models\ExportEntry;

it('it creates missing export entries', function (string $exportableClass) {
    $exportablesWithoutExport = $exportableClass::factory()->count(2)->create();
    $exportableWithExport = $exportableClass::factory()
        ->has(ExportEntry::factory(), 'exportEntry')
        ->create();

    expect(
        $exportablesWithoutExport->reduce(function (bool $carry, Exportable $exportable) {
            return $carry && $exportable->exportEntry === null;
        }, true)
    )->toBeTrue();


    (new GenerateExportEntries())->handle();

    expect(
        $exportablesWithoutExport->fresh()->reduce(function (bool $carry, Exportable $exportable) {
            return $carry && $exportable->exportEntry instanceof ExportEntry;
        }, true)
    )->toBeTrue();

    expect(ExportEntry::where('exportable_type', $exportableWithExport::class)->count())
        ->toBe(3);
})->with(ExportEntry::exportables());
