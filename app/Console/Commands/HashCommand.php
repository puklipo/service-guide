<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class HashCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:hash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate hash file for CSV files to track changes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $csvPath = resource_path('csv');
        $hashFilePath = resource_path('csv/hash.txt');
        
        // Get all CSV files in the directory
        $csvFiles = File::glob($csvPath . '/*.csv');
        
        if (empty($csvFiles)) {
            $this->warn('No CSV files found in resources/csv/');
            return;
        }
        
        $hashes = [];
        
        foreach ($csvFiles as $filePath) {
            $filename = basename($filePath);
            $hash = md5_file($filePath);
            $hashes[] = $filename . ':' . $hash;
            $this->info("Calculated hash for {$filename}");
        }
        
        // Sort the hashes by filename for consistent output
        sort($hashes);
        
        // Write to hash.txt
        $content = implode("\n", $hashes) . "\n";
        File::put($hashFilePath, $content);
        
        $this->info('Hash file generated successfully at resources/csv/hash.txt');
        $this->info('Total files processed: ' . count($csvFiles));
    }
}