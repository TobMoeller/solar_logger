<?php

use App\Models\Inverter;
use App\Models\InverterOutput;
use App\Models\InverterStatus;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Models\ExportEntry;

it('is implementes the exportable contract', function (string $exportableClass) {
    expect($exportableClass)
        ->toImplement(ExportableContract::class);
})->with(ExportEntry::exportables());

test('inverter has export data', function () {
    $inverter = Inverter::factory(['name' => '::name::'])
        ->create();

    expect($inverter)
        ->getExportResourcePath()->toBe('inverters')
        ->getExportData()->toMatchArray(['name' => '::name::']);
});

test('inverter output has export data', function () {
    $inverterOutput = InverterOutput::factory()
        ->create();

    expect($inverterOutput)
        ->getExportResourcePath()->toBe('inverter-outputs')
        ->getExportData()->toMatchArray($inverterOutput->toArray());
});

test('inverter status has export data', function () {
    $InverterStatus = InverterStatus::factory()
        ->create();

    expect($InverterStatus)
        ->getExportResourcePath()->toBe('inverter-status')
        ->getExportData()->toMatchArray($InverterStatus->toArray());
});
