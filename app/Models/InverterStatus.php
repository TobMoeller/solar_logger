<?php

namespace App\Models;

use Database\Factories\InverterStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Exceptions\MissingRelatedExportEntry;
use Modules\Export\Traits\Exportable;

class InverterStatus extends Model implements ExportableContract
{
    use Exportable;

    /** @use HasFactory<InverterStatusFactory> */
    use HasFactory;

    public $casts = [
        'is_online' => 'boolean',
    ];

    /**
     * @return BelongsTo<Inverter, $this>
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
        $inverterId = $this->inverter?->exportEntry?->server_id;

        if (! $inverterId) {
            throw new MissingRelatedExportEntry($this, $this->inverter ?? 'inverter');
        }

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
