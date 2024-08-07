<?php

namespace Modules\Export;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
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
                ->withHeader('Accept', 'application/json')
                ->withToken(Config::get('export.export_to_server.token'))
                ->timeout(Config::get('export.export_to_server.timeout'))
                ->connectTimeout(Config::get('export.export_to_server.connect_timeout'));
        });

        Cache::macro('exportLock', function (): Lock {
            return Cache::lock('export_exportables_lock', (24 * 60 * 60));
        });

        Cache::macro('restoreExportLock', function (string $lockOwner): Lock {
            return Cache::restoreLock('export_exportables_lock', $lockOwner);
        });
    }
}
