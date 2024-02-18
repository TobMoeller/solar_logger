<?php

namespace App\Services\InverterCommander;

use App\Enums\InverterCommand;
use App\Models\Inverter;

interface InverterCommanderContract
{
    public function send(Inverter $inverter, InverterCommand $command): mixed;
}
