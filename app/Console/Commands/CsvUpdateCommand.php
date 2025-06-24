<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;

class CsvUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update CSV files from WAM website';

    private string $baseUrl = 'https://www.wam.go.jp/content/files/pcpub/top/sfkopendata';
    private string $csvPath;
    private string $hashFile;
    private array $serviceIds;

    public function __construct()
    {
        parent::__construct();
        $this->csvPath = resource_path('csv');
        $this->hashFile = $this->csvPath . '/hash.txt';
        $this->serviceIds = array_keys(config('service'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting CSV update process...');

        // Generate current month pattern (YYYYMM)
        $currentMonth = now()->format('Ym');
        $previousMonth = now()->subMonth()->format('Ym');
        
        // Try current month first, then previous month
        $monthsToTry = [$currentMonth, $previousMonth];
        
        $updateNeeded = false;
        $newHashes = $this->loadCurrentHashes();

        foreach ($monthsToTry as $month) {
            $this->info("Trying month: {$month}");
            
            // Check service 11 first (as mentioned in requirements)
            $testServiceId = 11;
            $testHash = $this->checkServiceUpdate($testServiceId, $month);
            
            if ($testHash !== null) {
                $this->info("Found updates for month {$month}");
                
                // Check if hash has changed
                $currentFileName = "csvdownload0{$testServiceId}.csv";
                if (!isset($newHashes[$currentFileName]) || $newHashes[$currentFileName] !== $testHash) {
                    $this->info('Hash changed, updating all services...');
                    $updateNeeded = true;
                    
                    // Update all services for this month
                    foreach ($this->serviceIds as $serviceId) {
                        $hash = $this->downloadAndExtractService($serviceId, $month);
                        if ($hash) {
                            $fileName = "csvdownload0{$serviceId}.csv";
                            $newHashes[$fileName] = $hash;
                            $this->info("Updated service {$serviceId}");
                        }
                    }
                    break;
                } else {
                    $this->info('No updates needed - hashes match');
                    return 0;
                }
            }
        }

        if (!$updateNeeded) {
            $this->error('Could not find any valid CSV files to download');
            return 1;
        }

        // Update hash file
        $this->updateHashFile($newHashes);
        $this->info('CSV update completed successfully!');
        
        return 0;
    }

    /**
     * Check if a service has updates by downloading and checking hash
     */
    private function checkServiceUpdate(int $serviceId, string $month): ?string
    {
        $url = "{$this->baseUrl}/{$month}/sfkopendata_{$month}_{$serviceId}.zip";
        
        try {
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                return null;
            }

            $tempZipPath = tempnam(sys_get_temp_dir(), 'csv_check_');
            file_put_contents($tempZipPath, $response->body());

            $hash = $this->extractAndGetHash($tempZipPath, $serviceId);
            unlink($tempZipPath);

            return $hash;
        } catch (\Exception $e) {
            $this->error("Error checking service {$serviceId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Download and extract a service CSV file
     */
    private function downloadAndExtractService(int $serviceId, string $month): ?string
    {
        $url = "{$this->baseUrl}/{$month}/sfkopendata_{$month}_{$serviceId}.zip";
        
        try {
            $response = Http::timeout(60)->get($url);
            
            if (!$response->successful()) {
                $this->warn("Could not download service {$serviceId} from {$url}");
                return null;
            }

            $tempZipPath = tempnam(sys_get_temp_dir(), 'csv_update_');
            file_put_contents($tempZipPath, $response->body());

            $hash = $this->extractAndSave($tempZipPath, $serviceId);
            unlink($tempZipPath);

            return $hash;
        } catch (\Exception $e) {
            $this->error("Error downloading service {$serviceId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract ZIP and get hash without saving
     */
    private function extractAndGetHash(string $zipPath, int $serviceId): ?string
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath) !== TRUE) {
            return null;
        }

        // Look for CSV file in the ZIP
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    $zip->close();
                    return md5($content);
                }
            }
        }

        $zip->close();
        return null;
    }

    /**
     * Extract ZIP and save CSV file
     */
    private function extractAndSave(string $zipPath, int $serviceId): ?string
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath) !== TRUE) {
            return null;
        }

        // Look for CSV file in the ZIP
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    $csvFileName = "csvdownload0{$serviceId}.csv";
                    $csvPath = $this->csvPath . '/' . $csvFileName;
                    
                    file_put_contents($csvPath, $content);
                    $zip->close();
                    
                    return md5($content);
                }
            }
        }

        $zip->close();
        return null;
    }

    /**
     * Load current hashes from hash.txt
     */
    private function loadCurrentHashes(): array
    {
        if (!File::exists($this->hashFile)) {
            return [];
        }

        $hashes = [];
        $lines = file($this->hashFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$filename, $hash] = explode(':', $line, 2);
                $hashes[trim($filename)] = trim($hash);
            }
        }

        return $hashes;
    }

    /**
     * Update hash.txt file
     */
    private function updateHashFile(array $hashes): void
    {
        $content = '';
        foreach ($hashes as $filename => $hash) {
            $content .= "{$filename}:{$hash}\n";
        }

        file_put_contents($this->hashFile, $content);
    }
}