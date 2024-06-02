<?php

namespace Modules\Export\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Export\Exceptions\MissingRelatedExportEntry;
use Modules\Export\Models\ExportEntry;

interface ExportableContract
{
    /**
     * @return MorphOne<ExportEntry>
     */
    public function exportEntry(): MorphOne;

    /**
     * @throws MissingRelatedExportEntry
     * @return array<string, mixed>
     */
    public function getExportData(): array;

    public static function getExportResourcePath(): string;
}
