<?php

namespace App\Models;

use App\Enums\TimespanUnit;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Export\Contracts\ExportableContract;
use Modules\Export\Traits\Exportable;

class InverterOutput extends Model implements ExportableContract
{
    use HasFactory;
    use Exportable;

    public $casts = [
        'recorded_at' => 'date:Y-m-d',
        'timespan' => TimespanUnit::class,
    ];

    public $guarded = [];

    /**
     * @return BelongsTo<Inverter, InverterOutput>
     */
    public function inverter(): BelongsTo
    {
        return $this->belongsTo(Inverter::class);
    }

    public function scopeUpdatedToday(Builder $query): void
    {
        $query->whereDate('updated_at', now());
    }

    public static function getExportResourcePath(): string
    {
        return 'inverter-outputs';
    }

    public function getExportData(): array
    {
        return $this->toArray();
    }
}
