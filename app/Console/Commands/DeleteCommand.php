<?php

namespace App\Console\Commands;

use App\Models\Facility;
use Illuminate\Console\Command;

class DeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wam:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach (config('deleted') as $id) {
            $facility = Facility::where('no', $id)->first();
            if ($facility?->exists) {
                $facility->delete();
            }
        }

        return 0;
    }
}
