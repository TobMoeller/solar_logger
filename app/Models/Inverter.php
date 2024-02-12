<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inverter extends Model
{
    use HasFactory;

    /**
     * @return HasMany<InverterOutput>
     */
    public function outputs(): HasMany
    {
        return $this->hasMany(InverterOutput::class);
    }

    /**
     * @return HasMany<InverterStatus>
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(InverterStatus::class);
    }
}
