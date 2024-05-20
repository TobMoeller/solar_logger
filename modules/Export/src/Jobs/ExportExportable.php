<?php

namespace Modules\Export\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Export\Actions\ExportToServer;
use Modules\Export\Contracts\ExportableContract;

class ExportExportable implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 10, 60];
    }

    public function __construct(public ExportableContract&Model $exportable)
    {

    }

    public function handle(ExportToServer $exportToServer): void
    {
        $exportToServer->handle($this->exportable);
    }
}
