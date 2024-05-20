<?php

use Modules\Export\Models\ExportEntry;

use function Pest\Laravel\assertDatabaseHas;

it('has exportable relation', function (string $exportableClass) {
    $relatedModel = $exportableClass::factory()->create();
    $exportEntry = ExportEntry::factory()
        ->for($relatedModel, 'exportable')
        ->create();
    expect($exportEntry->exportable)
        ->toBeInstanceOf($exportableClass)
        ->is($relatedModel)->toBeTrue()
        ->and($relatedModel->exportEntry)
        ->toBeInstanceOf(ExportEntry::class)
        ->is($exportEntry)->toBeTrue();
})->with(ExportEntry::exportables());

it('creates an export entry for an exportable', function (string $exportableClass) {
    $exportable = $exportableClass::factory()->create();

    expect(ExportEntry::createFromExportable($exportable))
        ->toBeInstanceOf(ExportEntry::class);

    assertDatabaseHas('export_entries', [
        'exportable_id' => $exportable->id,
        'exportable_type' => $exportable::class,
    ]);
})->with(ExportEntry::exportables());
