<?php

namespace App\Services;

use App\Enums\InverterCommand;
use App\Exceptions\InverterUnreachable;
use App\Models\Inverter;
use App\Models\InverterStatus;
use Illuminate\Support\Facades\Log;
use Throwable;

class InverterMonitor
{
    public function __construct(public Inverter $inverter)
    {
        //
    }

    public function updateStatus(): self
    {
        $status = new InverterStatus();

        try {
            // @TODO fix type casting
            $status->udc = $this->inverter->command(InverterCommand::UDC);
            $status->idc = $this->inverter->command(InverterCommand::IDC);
            $status->pac = $this->inverter->command(InverterCommand::PAC);
            $status->pdc = $this->inverter->command(InverterCommand::PDC);
            $status->is_online = true;
        } catch (InverterUnreachable) {
            $status->is_online = false;
        } catch (Throwable $exception) {
            Log::error('Store Status failed', ['error' => $exception->getMessage()]);

            return $this;
        }

        $this->inverter->statuses()->save($status);

        return $this;
    }
}
