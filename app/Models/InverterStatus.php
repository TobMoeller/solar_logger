<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Exceptions\MissingRelatedExportEntry;
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
        throw_unless(
            $inverterId = $this->inverter?->exportEntry?->server_id,
            MissingRelatedExportEntry::class,
            $this, $this->inverter ?? 'inverter'
        );

        return [
            'inverter_id' => $inverterId,
            'is_online' => $this->is_online,
            'udc' => $this->udc,
            'idc' => $this->idc,
            'pac' => $this->pac,
            'pdc' => $this->pdc,
            'recorded_at' => $this->created_at?->toJSON(),
        ];
    }
}
