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
        $this->comment('閉鎖済み事業所を削除します。');

        foreach (config('deleted') as $deleted) {
            $facility = Facility::where('wam', $deleted['wam'])
                ->where('company_id', $deleted['company'])
                ->first();

            if ($facility?->exists) {
                $this->info($facility->name);
                $facility->delete();
            }
        }

        return 0;
    }
}
