<?php

namespace Modules\Export\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Models\ExportEntry;
use Throwable;

class GenerateExportEntries
{
    public function handle(): void
    {
        foreach (ExportEntry::exportables() as $exportableClass) {
            $exportableClass::query()
                ->whereDoesntHave('exportEntry')
                ->chunkById(200, function (Collection $exportables) {
                    $exportables->each(function (ExportableContract&Model $entry): void {
                        try {
                            ExportEntry::createFromExportable($entry);
                        } catch (Throwable $exception) {
                            Log::error(static::class.': Error creating ExportEntry', ['exportable' => $entry, 'exception' => $exception]);
                        }
                    });
                });
        }
    }
}
