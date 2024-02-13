<?php

namespace App\Console\Commands;

use App\Models\Inverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateInverter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-inverter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an inverter';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $data['ip'] = $this->ask('IP Address');
        $data['port'] = $this->ask('Port');

        $validator = Validator::make($data, [
            'ip' => ['required', 'ip'],
            'port' => ['required', 'integer', 'between:0,65535'],
        ]);

        $this->info(Inverter::forceCreate($validator->validated()));
    }
}
