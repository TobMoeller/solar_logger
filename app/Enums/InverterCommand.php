<?php

namespace App\Enums;

use App\Services\InverterCommander\Transformers\DivideFloatByTenTransformation;
use App\Services\InverterCommander\Transformers\FloatTransformation;
use App\Services\InverterCommander\Transformers\TransformationStrategyContract;
use Illuminate\Support\Carbon;

enum InverterCommand: string
{
    case UDC = 'REFU.GetParameter 1104'; // {"success": true, "data": "5.318821e+02"}
    case IDC = 'REFU.GetParameter 1105'; // {"success": true, "data": "8.157429e+00"}
    case PAC = 'REFU.GetParameter 1106'; // {"success": true, "data": "4.228308e+03"}
    case PDC = 'REFU.GetParameter 1107'; // {"success": true, "data": "4.284263e+03"}
    case YIELD_TODAY = 'REFU.GetParameter 1150,0'; // {"success": true, "data": "749"} - should be data/10
    case YIELD_YESTERDAY = 'REFU.GetParameter 1150,1'; // {"success": true, "data": "624"} - should be data/10
    case YIELD_MONTH = 'REFU.GetParameter 1153'; // {"success": true, "data": "1545"} - should be data/10
    case YIELD_YEAR = 'REFU.GetParameter 1154'; // {"success": true, "data": "13812"} - should be data/10

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

    public function transformationStrategy(): TransformationStrategyContract
    {
        return match (true) {
            $this->isOutputCommand() => app(DivideFloatByTenTransformation::class),
            default => app(FloatTransformation::class),
        };
    }
}
