<?php

namespace Modules\Export\Actions;

use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Export\Jobs\ClearExportLock;
use Modules\Export\Jobs\ExportExportable;
use Modules\Export\Models\ExportEntry;

class DispatchExportJobs
{
    public function handle(): void
    {
        $lock = Cache::exportLock();

        if (! $lock->get()) {
            Log::error(static::class.': Lock could not be akquired, previous export jobs are still running');
            return;
        }

        try {
            $jobChain = [];

            foreach (ExportEntry::exportables() as $exportableClass) {
                $table = (new $exportableClass)->getTable();

                $exportableClass::query()
                    ->where(function (EloquentBuilder $query) use ($exportableClass, $table) {
                        $query
                            ->where('updated_at', '>', function (QueryBuilder $query1) use ($exportableClass, $table) {
                                $query1->select('export_entries.exported_at')
                                    ->from('export_entries')
                                    ->whereColumn('export_entries.exportable_id', '=', $table.'.id')
                                    ->where('export_entries.exportable_type', '=', $exportableClass);
                            })
                            ->orWhereExists(function (QueryBuilder $query2) use ($exportableClass, $table) {
                                $query2->select(DB::raw(1))
                                    ->from('export_entries')
                                    ->whereNull('export_entries.server_id')
                                    ->whereColumn('export_entries.exportable_id', '=', $table.'.id')
                                    ->where('export_entries.exportable_type', '=', $exportableClass);
                            });
                    })
                    ->chunkById(200, function (Collection $exportables) use (&$jobChain) {
                        $jobs = $exportables->mapInto(ExportExportable::class);
                        $jobChain[] = Bus::batch($jobs)
                            ->allowFailures();
                    });
            }

            $jobChain[] = new ClearExportLock($lock->owner());

            Bus::chain($jobChain)
                ->catch(function () {
                    Cache::exportLock()->forceRelease();
                })
                ->dispatch();

        } catch (Exception $exception) {
            $lock->forceRelease();
            throw $exception;
        }
    }
}
