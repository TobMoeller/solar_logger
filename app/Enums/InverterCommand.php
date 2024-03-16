<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum InverterCommand: string
{
    case UDC = 'REFU.GetParameter 1104';
    case IDC = 'REFU.GetParameter 1105';
    case PAC = 'REFU.GetParameter 1106';
    case PDC = 'REFU.GetParameter 1107';
    case YIELD_TODAY = 'REFU.GetParameter 1150,0';
    case YIELD_YESTERDAY = 'REFU.GetParameter 1150,1';
    case YIELD_MONTH = 'REFU.GetParameter 1153';
    case YIELD_YEAR = 'REFU.GetParameter 1154';

    /**
     * @return static[]
     */
    public static function outputCommands(): array
    {
        return [
            self::YIELD_TODAY,
            self::YIELD_YESTERDAY,
            self::YIELD_MONTH,
            self::YIELD_YEAR,
        ];
    }

    public function isOutputCommand(): bool
    {
        return in_array($this, self::outputCommands());
    }

    public function getOutputTimespan(): ?TimespanUnit
    {
        return match ($this) {
            self::YIELD_TODAY, self::YIELD_YESTERDAY => TimespanUnit::DAY,
            self::YIELD_MONTH => TimespanUnit::MONTH,
            self::YIELD_YEAR => TimespanUnit::YEAR,
            default => null,
        };
    }

    public function getOutputDate(): ?Carbon
    {
        return match ($this) {
            self::YIELD_TODAY => now()->startOfDay(),
            self::YIELD_YESTERDAY => now()->yesterday(),
            self::YIELD_MONTH => now()->startOfMonth(),
            self::YIELD_YEAR => now()->startOfYear(),
            default => null,
        };
    }
}
