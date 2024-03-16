<?php

namespace App\Models;

use App\Enums\InverterCommand;
use App\Enums\TimespanUnit;
use App\Exceptions\InvalidRecordedAtDate;
use App\Services\InverterCommander;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Inverter extends Model
{
    use HasFactory;

    public $casts = [
        'is_monitored' => 'boolean',
    ];

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

    public function command(InverterCommand $command): mixed
    {
        return InverterCommander::send($this, $command);
    }

    /**
     * @return HasOne<InverterStatus>
     */
    public function latestStatus(): HasOne
    {
        return $this->statuses()->one()->ofMany('created_at', 'max');
    }

    /**
     * @return Attribute<bool, bool>
     */
    public function isOnline(): Attribute
    {
        return new Attribute(
            get: fn (): bool => $this->latestStatus?->is_online && $this->latestStatus->created_at?->greaterThanOrEqualTo(now()->subMinutes(30)),
        );
    }

    public function outputWasUpdatedToday(TimespanUnit $timespan, Carbon $recordedAt): bool
    {
        throw_unless(
            $timespan->isValidRecordedAtDate($recordedAt),
            InvalidRecordedAtDate::class,
            'Invalid recorded_at given'
        );

        return $this->outputs()
            ->where('timespan', $timespan)
            ->whereDate('recorded_at', $recordedAt)
            ->updatedToday()
            ->exists();
    }
}
