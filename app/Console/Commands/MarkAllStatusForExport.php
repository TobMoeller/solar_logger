<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkAllStatusForExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-all-status-for-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $confirmed = $this->confirm('Do you really want to trigger an export for all inverter statuses?');

        if (! $confirmed) {
            $this->info('Cancelled');

            return;
        }

        DB::table('inverter_statuses')->update(['updated_at' => now()]);

        $this->info('All inverter statuses were updated');
    }
}
