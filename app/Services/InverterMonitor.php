<?php

namespace App\Services;

use App\Actions\UpdateOrCreateInverterOutput;
use App\Enums\InverterCommand;
use App\Enums\TimespanUnit;
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
        } catch (InverterUnreachable $exception) {
            Log::debug('Inverter unreachable', ['inverter' => $this->inverter, 'error' => $exception->getMessage()]);
            $status->is_online = false;
        } catch (Throwable $exception) {
            Log::error('Store Status failed', ['inverter' => $this->inverter, 'error' => $exception->getMessage()]);

            return $this;
        }

        $this->inverter->statuses()->save($status);

        return $this;
    }

    public function updateOutput(): self
    {
        if (! $this->inverter->is_online) {
            return $this;
        }

        $updateOrCreateInverterOutput = app(UpdateOrCreateInverterOutput::class);

        if ($outputToday = $this->inverter->command(InverterCommand::YIELD_TODAY)) {
            $updateOrCreateInverterOutput->handle(
                $this->inverter,
                TimespanUnit::DAY,
                now()->startOfDay(),
                $outputToday
            );
        }

        if (! $this->inverter->outputWasUpdatedToday(TimespanUnit::DAY, $date = now()->yesterday()) &&
            $outputYesterday = $this->inverter->command(InverterCommand::YIELD_YESTERDAY)
        ) {
            $updateOrCreateInverterOutput->handle(
                $this->inverter,
                TimespanUnit::DAY,
                $date,
                $outputYesterday
            );
        }

        if (! $this->inverter->outputWasUpdatedToday(TimespanUnit::MONTH, $date = now()->startOfMonth()) &&
            $outputMonth = $this->inverter->command(InverterCommand::YIELD_MONTH)
        ) {
            $updateOrCreateInverterOutput->handle(
                $this->inverter,
                TimespanUnit::MONTH,
                $date,
                $outputMonth
            );
        }

        if (! $this->inverter->outputWasUpdatedToday(TimespanUnit::YEAR, $date = now()->startOfYear()) &&
            $outputYear = $this->inverter->command(InverterCommand::YIELD_YEAR)
        ) {
            $updateOrCreateInverterOutput->handle(
                $this->inverter,
                TimespanUnit::YEAR,
                $date,
                $outputYear
            );
        }

        return $this;
    }
}
