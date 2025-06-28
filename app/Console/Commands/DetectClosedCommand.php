<?php

namespace App\Console\Commands;

use App\Models\Facility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DetectClosedCommand extends Command
{
    protected $signature = 'wam:detect-closed {--delete : 検出した閉鎖済み事業所を削除する}';

    protected $description = 'CSVに存在しない閉鎖済み事業所を検出し、config/deleted.phpを更新します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // CSVから全事業所番号を抽出
        $this->info('CSVから全事業所番号を収集中...');
        $csvNumbers = $this->getAllCsvNumbers();
        $this->info(sprintf('CSV内の事業所数: %s', number_format(count($csvNumbers))));

        // DBから全事業所番号を取得
        $this->info('データベースから事業所番号を取得中...');
        $dbNumbers = Facility::pluck('no')->toArray();
        $this->info(sprintf('データベース内の事業所数: %s', number_format(count($dbNumbers))));

        // CSVに存在しない事業所を特定
        $closedNumbers = array_values(array_diff($dbNumbers, $csvNumbers));
        $this->info(sprintf('検出された閉鎖済み事業所数: %s', number_format(count($closedNumbers))));

        if (empty($closedNumbers)) {
            $this->info('閉鎖済み事業所は検出されませんでした。');
            return 0;
        }

        // 既存のconfigと統合
        $existingDeleted = config('deleted', []);
        $allDeleted = array_values(array_unique(array_merge($existingDeleted, $closedNumbers)));
        $this->info(sprintf('config/deleted.phpに保存する事業所数: %s', number_format(count($allDeleted))));

        // config/deleted.phpを更新
        $this->updateDeletedConfig($allDeleted);
        $this->info('config/deleted.phpを更新しました。');

        // --deleteオプションが指定されている場合は削除も実行
        if ($this->option('delete')) {
            $this->call('wam:delete');
        } else {
            $this->info('閉鎖済み事業所を削除するには `php artisan wam:delete` を実行してください。');
        }

        return 0;
    }

    private function getAllCsvNumbers(): array
    {
        $allNumbers = [];
        $csvPath = resource_path('csv/'.config('wam.current'));
        $files = glob($csvPath.'/*.csv');

        foreach ($files as $file) {
            $this->comment(sprintf('処理中: %s', basename($file)));
            $handle = fopen($file, 'r');

            // ヘッダー行をスキップ
            $headers = fgetcsv($handle);
            $noIndex = array_search('事業所番号', $headers);

            if ($noIndex === false) {
                $this->warn(sprintf('ファイル %s に "事業所番号" 列が見つかりません', basename($file)));
                continue;
            }

            while (($data = fgetcsv($handle)) !== false) {
                if (isset($data[$noIndex]) && is_numeric($data[$noIndex])) {
                    $allNumbers[] = (int) $data[$noIndex];
                }
            }

            fclose($handle);
        }

        return array_unique($allNumbers);
    }

    private function updateDeletedConfig(array $numbers): void
    {
        sort($numbers);
        $content = "<?php\n\n// 閉鎖済みなどでインポート時に除外する事業所番号\nreturn [\n";
        foreach ($numbers as $number) {
            $content .= "    {$number},\n";
        }
        $content .= "];\n";

        file_put_contents(config_path('deleted.php'), $content);
    }
}
