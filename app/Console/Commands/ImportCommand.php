<?php

namespace App\Console\Commands;

use App\Jobs\ImportJob;
use Illuminate\Console\Command;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wam:import';

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
        collect(config('service'))->each(function ($name, $id) {
            $csv = resource_path('csv/csvdownload0'.$id.'.csv');

            if (!file_exists($csv)) {
                return;
            }

            $this->info($name);
            ImportJob::dispatch($id);
        });
    }
}
