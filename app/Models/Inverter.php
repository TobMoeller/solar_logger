<?php

namespace App\Models;

use App\Enums\InverterCommand;
use App\Exceptions\InvalidInverterCommand;
use App\Services\InverterCommander;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function outputWasUpdatedToday(InverterCommand $command): bool
    {
        throw_unless(
            $command->isOutputCommand(),
            InvalidInverterCommand::class,
            'Invalid command'
        );

        return $this->outputs()
            ->where('timespan', $command->getOutputTimespan())
            ->whereDate('recorded_at', $command->getOutputDate())
            ->updatedToday()
            ->exists();
    }

    /**
     * @param  Builder<Inverter>  $query
     */
    public function scopeIsOfflineForOneDay(Builder $query): void
    {
        $query->whereDoesntHave('statuses', function (Builder $query) {
            $query->where('is_online', true)
                ->where('created_at', '>=', now()->subDay());
        });
    }
}
