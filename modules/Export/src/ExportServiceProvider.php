<?php

namespace Modules\Export;

use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');
    }
}