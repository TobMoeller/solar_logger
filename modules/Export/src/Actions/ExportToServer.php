<?php

namespace Modules\Export\Actions;

use Illuminate\Support\Facades\Http;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Exceptions\ExportFailed;
use Modules\Export\Exceptions\MissingExportEntry;
use Modules\Export\Exceptions\MissingRelatedExportEntry;

class ExportToServer
{
    /**
     * @throws MissingExportEntry
     * @throws MissingRelatedExportEntry
     * @throws ExportFailed
     */
    public function handle(ExportableContract $exportable): void
    {
        throw_if(empty($exportable->exportEntry), MissingExportEntry::class, $exportable);

        $client = Http::exportServer();

        $path = $exportable::getExportResourcePath();
        $exportData = $exportable->getExportData();

        if ($exportable->exportEntry->hasServerId()) {
            $response = $client->put($path.'/'.$exportable->exportEntry->server_id, $exportData);
        } else {
            $response = $client->post($path, $exportData);
        }

        throw_unless(
            $response->successful() && $serverId = $response->json('data.id', false),
            ExportFailed::class,
            $exportable,
            $response
        );

        $exportable->exportEntry->update([
            'server_id' => $serverId,
            'exported_at' => now(),
        ]);
    }
}
