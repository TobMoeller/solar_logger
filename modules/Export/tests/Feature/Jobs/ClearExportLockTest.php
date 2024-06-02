<?php

use Illuminate\Cache\NoLock;
use Illuminate\Support\Facades\Cache;
use Modules\Export\Jobs\ClearExportLock;

it('clears the export lock', function () {
    Cache::shouldReceive('restoreExportLock')
        ->once()
        ->with('::owner::')
        ->andReturn(new NoLock('test', 5));

    ClearExportLock::dispatch('::owner::');
});
