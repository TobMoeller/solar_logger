<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InverterOutput extends Model
{
    use HasFactory;

    public $casts = [
        'recorded_at' => 'date',
    ];

    /**
     * @return BelongsTo<Inverter, InverterOutput>
     */
    public function inverter(): BelongsTo
    {
        return $this->belongsTo(Inverter::class);
    }
}
