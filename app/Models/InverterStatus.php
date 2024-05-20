<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Traits\Exportable;

class InverterStatus extends Model implements ExportableContract
{
    use Exportable;
    use HasFactory;

    public $casts = [
        'is_online' => 'boolean',
    ];

    /**
     * @return BelongsTo<Inverter, InverterStatus>
     */
    public function inverter(): BelongsTo
    {
        return $this->belongsTo(Inverter::class);
    }

    public static function getExportResourcePath(): string
    {
        return 'inverter-status';
    }

    public function getExportData(): array
    {
        return $this->toArray();
    }
}
