<?php

namespace Modules\Export;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');

        Http::macro('exportServer', function (): PendingRequest {
            return Http::baseUrl(Config::get('export.export_to_server.base_url'))
                ->withToken(Config::get('export.export_to_server.token'))
                ->timeout(Config::get('export.export_to_server.timeout'))
                ->connectTimeout(Config::get('export.export_to_server.connect_timeout'));
        });
    }
}
