<?php

use Modules\Export\Models\ExportEntry;

it('has exportable relation', function (string $exportable) {
    $relatedModel = $exportable::factory()->create();
    $exportEntry = ExportEntry::factory()
        ->for($relatedModel, 'exportable')
        ->create();
    expect($exportEntry->exportable)
        ->toBeInstanceOf($exportable)
        ->is($relatedModel)->toBeTrue()
        ->and($relatedModel->exportEntry)
        ->toBeInstanceOf(ExportEntry::class)
        ->is($exportEntry)->toBeTrue();
})->with(ExportEntry::exportables());
