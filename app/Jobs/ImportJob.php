<?php

namespace App\Jobs;

use App\Imports\WamImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $csv = resource_path('csv/csvdownload0'.$this->id.'.csv');

        if (! file_exists($csv)) {
            return;
        }

        info(config('service.'.$this->id));

        app(WamImport::class, ['service_id' => $this->id])->import($csv);
    }
}
