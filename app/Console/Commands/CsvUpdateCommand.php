<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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

    /**
     * html4, Shift-JIS
     */
    private string $discoveryUrl = 'https://www.wam.go.jp/content/wamnet/pcpub/top/sfkopendata/';

    private string $csvPath;

    private array $serviceIds;

    public function __construct()
    {
        parent::__construct();
        $this->csvPath = resource_path('csv');
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

        // Compare with current config
        $currentPeriod = config('wam.current');
        if ($datePeriod === $currentPeriod) {
            $this->info('No updates needed - current period matches discovered period');

            return 0;
        }

        $this->info("New period detected: {$datePeriod} (current: {$currentPeriod})");

        // Create new directory for the period
        $newPeriodPath = $this->csvPath.'/'.$datePeriod;
        if (! file_exists($newPeriodPath)) {
            mkdir($newPeriodPath, 0755, true);
            $this->info("Created directory: {$newPeriodPath}");
        }

        // Download all services for the new period
        $successCount = 0;
        foreach ($this->serviceIds as $serviceId) {
            if ($this->downloadAndExtractService($serviceId, $datePeriod)) {
                $successCount++;
                $this->info("Downloaded service {$serviceId}");
            }
        }

        if ($successCount === 0) {
            $this->error('Could not download any CSV files');

            return 1;
        }

        // Update config file
        $this->updateConfigFile($datePeriod);
        $this->info("CSV update completed successfully! Updated to period: {$datePeriod}");

        return 0;
    }

    /**
     * Discover current data period by scraping the main WAM page
     */
    private function discoverCurrentDataPeriod(): ?string
    {
        try {
            $response = Http::timeout(30)->get($this->discoveryUrl);

            if (! $response->successful()) {
                $this->error('Failed to fetch discovery page');

                return null;
            }

            $html = $response->body();

            // Use DOMDocument to parse HTML more robustly
            $dom = new DOMDocument;
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
            $this->error('Error discovering data period: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Download and extract a service CSV file
     */
    private function downloadAndExtractService(int $serviceId, string $month): bool
    {
        $url = "{$this->baseUrl}/{$month}/sfkopendata_{$month}_{$serviceId}.zip";

        try {
            $response = Http::timeout(60)->get($url);

            if (! $response->successful()) {
                $this->warn("Could not download service {$serviceId} from {$url}");

                return false;
            }

            $tempZipPath = tempnam(sys_get_temp_dir(), 'csv_update_');
            file_put_contents($tempZipPath, $response->body());

            $success = $this->extractAndSave($tempZipPath, $serviceId, $month);

            // Validate the file path before unlinking
            if (is_file($tempZipPath) && strpos(realpath($tempZipPath), sys_get_temp_dir()) === 0) {
                unlink($tempZipPath);
            }

            return $success;
        } catch (\Exception $e) {
            $this->error("Error downloading service {$serviceId}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Extract ZIP and save CSV file
     */
    private function extractAndSave(string $zipPath, int $serviceId, string $month): bool
    {
        $zip = new ZipArchive;

        try {
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open ZIP file');
            }

            // Look for CSV file in the ZIP
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        $csvFileName = "csvdownload0{$serviceId}.csv";
                        $csvPath = $this->csvPath.'/'.$month.'/'.$csvFileName;

                        // Add error handling for file writing
                        if (file_put_contents($csvPath, $content) === false) {
                            throw new \Exception("Failed to write CSV file: {$csvPath}");
                        }

                        $zip->close();

                        return true;
                    }
                }
            }

            $zip->close();

            return false;
        } catch (\Exception $e) {
            $this->error('Error saving CSV file: '.$e->getMessage());
            if (isset($zip)) {
                $zip->close();
            }

            return false;
        }
    }

    /**
     * Update config/wam.php file with new current period
     */
    private function updateConfigFile(string $newPeriod): void
    {
        try {
            // wam.phpはcurrent専用。ここで上書きするので他の用途には使わない。
            $configPath = config_path('wam.php');
            $configContent = "<?php\n\nreturn [\n    'current' => '{$newPeriod}',\n];\n";

            if (file_put_contents($configPath, $configContent) === false) {
                throw new \RuntimeException("Failed to write to config file: {$configPath}");
            }

            $this->info("Updated config/wam.php with current period: {$newPeriod}");
        } catch (\Exception $e) {
            $this->error('Error updating config file: '.$e->getMessage());
            throw $e;
        }
    }
}
