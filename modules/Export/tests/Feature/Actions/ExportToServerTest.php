<?php

use App\Models\Inverter;
use App\Models\InverterOutput;
use App\Models\InverterStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Export\Actions\ExportToServer;
use Modules\Export\Exceptions\ExportFailed;
use Modules\Export\Exceptions\MissingExportEntry;
use Modules\Export\Models\ExportEntry;

beforeEach(function () {
    Config::set('export.export_to_server.base_url', $this->fakeUrl = 'http://foo.bar');
});

it('throws an exception for missing export entry', function (string $exportableClass) {
    $exportable = $exportableClass::factory()->create();

    expect(fn () => (new ExportToServer())->handle($exportable))
        ->toThrow(MissingExportEntry::class);
})->with(ExportEntry::exportables());

it('throws an exception for failed requests', function (string $exportableClass) {
    Http::fake([
        $this->fakeUrl.'*' => Http::response([], 400),
    ]);

    $exportable = $exportableClass::factory()
        ->when(
            in_array($exportableClass, [InverterOutput::class, InverterStatus::class]),
            fn (Factory $factory) => $factory->for(Inverter::factory()->has(ExportEntry::factory()))
        )
        ->has(ExportEntry::factory(), 'exportEntry')
        ->create();

    expect(fn () => (new ExportToServer())->handle($exportable))
        ->toThrow(ExportFailed::class);
})->with(ExportEntry::exportables());

it('sends a post request for new exports', function (string $exportableClass) {
    Carbon::setTestNow(now());

    $responseFile = match ($exportableClass) {
        Inverter::class => 'inverters',
        InverterOutput::class => 'inverter_outputs',
        InverterStatus::class => 'inverter_status',
    };

    $responseData = json_decode(file_get_contents(base_path("modules/Export/tests/Fixtures/ApiResponses/{$responseFile}.json")), true);
    $responseData['data']['id'] = 123;

    Http::fake([
        $this->fakeUrl.'*' => Http::response($responseData, 201),
    ]);

    $exportable = $exportableClass::factory()
        ->when(
            in_array($exportableClass, [InverterOutput::class, InverterStatus::class]),
            fn (Factory $factory) => $factory->for(Inverter::factory()->has(ExportEntry::factory()))
        )
        ->create();

    $exportEntry = ExportEntry::factory()
        ->for($exportable, 'exportable')
        ->state(['server_id' => null, 'exported_at' => null])
        ->create();

    (new ExportToServer())->handle($exportable);

    expect($exportEntry->fresh())
        ->server_id->toBe(123)
        ->exported_at->eq(now()->toDateTimeString())->toBeTrue();

    Http::assertSent(function (Request $request) use ($exportableClass) {
        return $request->method() === 'POST' &&
            $request->url() === 'http://foo.bar/'.$exportableClass::getExportResourcePath();
    });

    Carbon::setTestNow();
})->with(ExportEntry::exportables());

it('sends a put request for existing exports', function (string $exportableClass) {
    Carbon::setTestNow(now());

    $responseFile = match ($exportableClass) {
        Inverter::class => 'inverters',
        InverterOutput::class => 'inverter_outputs',
        InverterStatus::class => 'inverter_status',
    };

    $responseData = json_decode(file_get_contents(base_path("modules/Export/tests/Fixtures/ApiResponses/{$responseFile}.json")), true);
    $responseData['data']['id'] = 123;

    Http::fake([
        $this->fakeUrl.'*' => Http::response($responseData, 200),
    ]);

    $exportable = $exportableClass::factory()
        ->when(
            in_array($exportableClass, [InverterOutput::class, InverterStatus::class]),
            fn (Factory $factory) => $factory->for(Inverter::factory()->has(ExportEntry::factory()))
        )
        ->create();

    $exportEntry = ExportEntry::factory()
        ->for($exportable, 'exportable')
        ->state(['server_id' => 123, 'exported_at' => now()])
        ->create();

    (new ExportToServer())->handle($exportable);

    expect($exportEntry->fresh())
        ->server_id->toBe(123)
        ->exported_at->eq(now()->toDateTimeString())->toBeTrue();

    Http::assertSent(function (Request $request) use ($exportableClass) {
        return $request->method() === 'PUT' &&
            $request->url() === 'http://foo.bar/'.$exportableClass::getExportResourcePath().'/123';
    });

    Carbon::setTestNow();
})->with(ExportEntry::exportables());
