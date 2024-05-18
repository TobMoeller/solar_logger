<?php

namespace Modules\Export\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Export\Models\ExportEntry;

trait IsExportable
{
    /**
     * @return MorphOne<ExportEntry>
     */
    public function exportEntry(): MorphOne
    {
        return $this->morphOne(ExportEntry::class, 'exportable');
    }
}
