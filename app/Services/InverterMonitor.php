<?php

namespace App\Services;

use App\Actions\UpdateOrCreateInverterOutput;
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

        $this->updateOutputFor(InverterCommand::YIELD_TODAY);
        $this->updateOutputOnceADayFor(InverterCommand::YIELD_YESTERDAY);
        $this->updateOutputOnceADayFor(InverterCommand::YIELD_MONTH);
        $this->updateOutputOnceADayFor(InverterCommand::YIELD_YEAR);

        return $this;
    }

    protected function updateOutputFor(InverterCommand $command): void
    {
        if (($output = $this->inverter->command($command)) === null) {
            return;
        }

        app(UpdateOrCreateInverterOutput::class)
            ->handle(
                $this->inverter,
                $command,
                $output
            );
    }

    protected function updateOutputOnceADayFor(InverterCommand $command): void
    {
        if ($this->inverter->outputWasUpdatedToday($command)) {
            return;
        }

        $this->updateOutputFor($command);
    }
}
