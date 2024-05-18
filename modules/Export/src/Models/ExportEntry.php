<?php

namespace Modules\Export\Models;

use App\Models\Inverter;
use App\Models\InverterOutput;
use App\Models\InverterStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Export\Database\Factories\ExportEntryFactory;

class ExportEntry extends Model
{
    use HasFactory;

    public $casts = [
        'exported_at' => 'datetime',
    ];

    protected static function newFactory(): ExportEntryFactory
    {
        return ExportEntryFactory::new();
    }

    /**
     * @return array<int, class-string>
     */
    public final static function exportables(): array
    {
        return [
            Inverter::class,
            InverterOutput::class,
            InverterStatus::class,
        ];
    }

    /**
     * @return MorphTo<Model, ExportEntry>
     */
    public function exportable(): MorphTo
    {
        return $this->morphTo();
    }
}
