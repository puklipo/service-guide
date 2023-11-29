<?php

namespace App\Console\Commands;

use App\Jobs\ImportJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

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
        $jobs = collect(config('service'))
            ->filter(fn ($name, $id) => file_exists(resource_path('csv/csvdownload0'.$id.'.csv')))
            ->map(function ($name, $id) {
                $this->info($name);

                return new ImportJob($id);
            });

        Bus::chain($jobs)->dispatch();
    }
}
