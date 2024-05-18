<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Export\Traits\IsExportable;

class InverterStatus extends Model
{
    use HasFactory;
    use IsExportable;

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
}
