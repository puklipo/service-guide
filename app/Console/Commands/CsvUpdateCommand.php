<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;
use DOMDocument;
use DOMXPath;

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
    private string $discoveryUrl = 'https://www.wam.go.jp/content/wamnet/pcpub/top/sfkopendata/';
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

        // Discover the current data period from the main page
        $datePeriod = $this->discoverCurrentDataPeriod();
        
        if ($datePeriod === null) {
            $this->error('Could not discover current data period from WAM website');
            return 1;
        }
        
        $this->info("Discovered data period: {$datePeriod}");
        
        $updateNeeded = false;
        $newHashes = $this->loadCurrentHashes();

        // Check service 11 first (as mentioned in requirements)
        $testServiceId = 11;
        $testHash = $this->checkServiceUpdate($testServiceId, $datePeriod);
        
        if ($testHash !== null) {
            $this->info("Found updates for period {$datePeriod}");
            
            // Check if hash has changed
            $currentFileName = "csvdownload0{$testServiceId}.csv";
            if (!isset($newHashes[$currentFileName]) || $newHashes[$currentFileName] !== $testHash) {
                $this->info('Hash changed, updating all services...');
                $updateNeeded = true;
                
                // Update all services for this period
                foreach ($this->serviceIds as $serviceId) {
                    $hash = $this->downloadAndExtractService($serviceId, $datePeriod);
                    if ($hash) {
                        $fileName = "csvdownload0{$serviceId}.csv";
                        $newHashes[$fileName] = $hash;
                        $this->info("Updated service {$serviceId}");
                    }
                }
            } else {
                $this->info('No updates needed - hashes match');
                return 0;
            }
        } else {
            $this->error('Could not find any valid CSV files to download');
            return 1;
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
     * Discover current data period by scraping the main WAM page
     */
    private function discoverCurrentDataPeriod(): ?string
    {
        try {
            $response = Http::timeout(30)->get($this->discoveryUrl);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch discovery page');
                return null;
            }

            $html = $response->body();
            
            // Use DOMDocument to parse HTML more robustly
            $dom = new DOMDocument();
            // Suppress warnings for malformed HTML
            libxml_use_internal_errors(true);
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            
            $xpath = new DOMXPath($dom);
            
            // Look for links with .zip extension
            $zipLinks = $xpath->query('//a[contains(@href, ".zip")]');
            
            foreach ($zipLinks as $link) {
                $href = $link->getAttribute('href');
                
                // Look for pattern like: /content/files/pcpub/top/sfkopendata/202503/sfkopendata_202503_11.zip
                if (preg_match('/\/content\/files\/pcpub\/top\/sfkopendata\/(\d{6})\/sfkopendata_\d{6}_\d+\.zip/', $href, $matches)) {
                    $this->info("Found zip URL: {$href}");
                    return $matches[1]; // Return the YYYYMM pattern
                }
            }
            
            $this->error('No suitable zip URLs found in the discovery page');
            return null;
            
        } catch (\Exception $e) {
            $this->error('Error discovering data period: ' . $e->getMessage());
            return null;
        }
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
            
            // Validate the file path before unlinking
            if (is_file($tempZipPath) && strpos(realpath($tempZipPath), sys_get_temp_dir()) === 0) {
                unlink($tempZipPath);
            }

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
            
            // Validate the file path before unlinking
            if (is_file($tempZipPath) && strpos(realpath($tempZipPath), sys_get_temp_dir()) === 0) {
                unlink($tempZipPath);
            }

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
        
        try {
            if ($zip->open($zipPath) !== TRUE) {
                throw new \Exception("Failed to open ZIP file");
            }

            // Look for CSV file in the ZIP
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        $zip->close();
                        // Use SHA-256 instead of MD5 for better security
                        return hash('sha256', $content);
                    }
                }
            }

            $zip->close();
            return null;
        } catch (\Exception $e) {
            $this->error("Error extracting ZIP file: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract ZIP and save CSV file
     */
    private function extractAndSave(string $zipPath, int $serviceId): ?string
    {
        $zip = new ZipArchive();
        
        try {
            if ($zip->open($zipPath) !== TRUE) {
                throw new \Exception("Failed to open ZIP file");
            }

            // Look for CSV file in the ZIP
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        $csvFileName = "csvdownload0{$serviceId}.csv";
                        $csvPath = $this->csvPath . '/' . $csvFileName;
                        
                        // Add error handling for file writing
                        if (file_put_contents($csvPath, $content) === false) {
                            throw new \Exception("Failed to write CSV file: {$csvPath}");
                        }
                        
                        $zip->close();
                        
                        // Use SHA-256 instead of MD5 for better security
                        return hash('sha256', $content);
                    }
                }
            }

            $zip->close();
            return null;
        } catch (\Exception $e) {
            $this->error("Error saving CSV file: " . $e->getMessage());
            if (isset($zip)) {
                $zip->close();
            }
            return null;
        }
    }

    /**
     * Load current hashes from hash.txt
     */
    private function loadCurrentHashes(): array
    {
        if (!File::exists($this->hashFile)) {
            return [];
        }

        try {
            $hashes = [];
            // Validate the file path for security
            $validatedPath = realpath($this->hashFile);
            if ($validatedPath === false || !str_starts_with($validatedPath, realpath(resource_path('csv')))) {
                throw new \Exception('Invalid hash file path');
            }
            
            $lines = file($validatedPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    [$filename, $hash] = explode(':', $line, 2);
                    $hashes[trim($filename)] = trim($hash);
                }
            }

            return $hashes;
        } catch (\Exception $e) {
            $this->error('Error loading hash file: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update hash.txt file
     */
    private function updateHashFile(array $hashes): void
    {
        try {
            $content = '';
            foreach ($hashes as $filename => $hash) {
                $content .= "{$filename}:{$hash}\n";
            }

            if (file_put_contents($this->hashFile, $content) === false) {
                throw new \RuntimeException("Failed to write to hash file: {$this->hashFile}");
            }
        } catch (\Exception $e) {
            $this->error('Error updating hash file: ' . $e->getMessage());
            throw $e;
        }
    }
}