<?php

namespace App\Console\Commands;

use App\Jobs\ImportJob;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Throwable;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wam:import {service?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws Throwable
     */
    public function handle(): void
    {
        $jobs = collect(config('service'))
            ->when($this->argument('service'), function (Collection $collection) {
                return $collection->filter(fn (string $value, int $key) => $key === (int) $this->argument('service'));
            })
            ->filter(fn ($name, $id) => file_exists(resource_path('csv/csvdownload0'.$id.'.csv')))
            ->map(function ($name, $id) {
                $this->info($name);

                return new ImportJob($id);
            });

        $batch = Bus::batch($jobs)
            ->before(function (Batch $batch) {
                // The batch has been created but no jobs have been added...
                info('before : '.$batch->createdAt);
            })->progress(function (Batch $batch) {
                // A single job has completed successfully...
                info('progress : '.$batch->id);
            })->then(function (Batch $batch) {
                // All jobs completed successfully...
                info('then : '.$batch->id);
            })->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
                info('catch : '.$e->getMessage());
            })->finally(function (Batch $batch) {
                // The batch has finished executing...
                info('finally : '.$batch->finishedAt);
            })->dispatch();

        $this->info($batch->id);
    }
}
