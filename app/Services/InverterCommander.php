<?php

namespace App\Services;

use App\Services\InverterCommander\InverterCommanderContract;
use Illuminate\Support\Facades\Facade;

class InverterCommander extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InverterCommanderContract::class;
    }
}
