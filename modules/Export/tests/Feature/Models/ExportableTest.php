<?php

use App\Models\Inverter;
use App\Models\InverterOutput;
use App\Models\InverterStatus;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Exceptions\MissingRelatedExportEntry;
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
    $inverter = Inverter::factory()->has(ExportEntry::factory())->create();
    $inverterOutput = InverterOutput::factory()
        ->for($inverter)
        ->create();

    expect($inverterOutput)
        ->getExportResourcePath()->toBe('inverter-outputs')
        ->getExportData()->toMatchArray([
            'inverter_id' => $inverter->exportEntry->server_id,
            'output' => $inverterOutput->output,
            'timespan' => $inverterOutput->timespan,
            'recorded_at' => $inverterOutput->recorded_at->toDateString(),
        ]);
});

test('inverter status has export data', function () {
    $inverter = Inverter::factory()->has(ExportEntry::factory())->create();
    $InverterStatus = InverterStatus::factory()
        ->for($inverter)
        ->create();

    expect($InverterStatus)
        ->getExportResourcePath()->toBe('inverter-status')
        ->getExportData()->toMatchArray([
            'inverter_id' => $inverter->exportEntry->server_id,
            'is_online' => $InverterStatus->is_online,
            'udc' => $InverterStatus->udc,
            'idc' => $InverterStatus->idc,
            'pac' => $InverterStatus->pac,
            'pdc' => $InverterStatus->pdc,
            'recorded_at' => $InverterStatus->created_at->toJSON(),
        ]);
});

it('throws a missing related export entry exception', function (string $class) {
    $model = $class::factory()->create();

    expect(fn () => $model->getExportData())
        ->toThrow(MissingRelatedExportEntry::class);
})->with(fn () => [
    InverterOutput::class,
    InverterStatus::class,
]);
