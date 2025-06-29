<?php

namespace App\Console\Commands;

use App\Models\Facility;
use Illuminate\Console\Command;

class DetectClosedCommand extends Command
{
    protected $signature = 'wam:detect-closed {--delete : 検出した閉鎖済み事業所を削除する}';

    protected $description = 'CSVに存在しないWAM NOと法人番号の組み合わせを検出し、config/deleted.phpを更新します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // CSVからWAM NOと法人番号の組み合わせを抽出
        $this->info('CSVからWAM NOと法人番号の組み合わせを収集中...');
        $csvData = $this->getAllCsvWamCompanyPairs();
        $this->info(sprintf('CSV内の事業所数: %s', number_format(count($csvData))));

        // DBからWAM NOと法人番号の組み合わせを取得
        $this->info('データベースからWAM NOと法人番号の組み合わせを取得中...');
        $dbData = Facility::select(['wam', 'company_id', 'service_id', 'area_id'])->get()->map(function ($facility) {
            return $facility->wam.'-'.$facility->company_id;
        })->toArray();
        $this->info(sprintf('データベース内の事業所数: %s', number_format(count($dbData))));

        // CSVに存在しない事業所を特定
        $closedFacilityKeys = array_values(array_diff($dbData, $csvData));
        $this->info(sprintf('検出された閉鎖済み事業所数: %s', number_format(count($closedFacilityKeys))));

        if (empty($closedFacilityKeys)) {
            $this->info('閉鎖済み事業所は検出されませんでした。');

            return 0;
        }

        // 閉鎖された事業所のWAM IDと法人番号のペアを作成
        $closedFacilities = [];
        foreach ($closedFacilityKeys as $key) {
            [$wam, $companyId] = explode('-', $key);
            $closedFacilities[] = [
                'wam' => $wam,
                'company' => $companyId,
            ];
        }

        $this->info(sprintf('WAM IDと法人番号のペア数: %s', number_format(count($closedFacilities))));

        // 既存のconfigと統合
        $existingDeleted = []; // config('deleted', []);
        $allDeleted = array_merge($existingDeleted, $closedFacilities);

        // 重複除去（WAMと法人番号の組み合わせで一意になるようにする）
        $uniqueDeleted = [];
        $keys = [];

        foreach ($allDeleted as $item) {
            $key = $item['wam'].'-'.$item['company'];
            if (! in_array($key, $keys)) {
                $keys[] = $key;
                $uniqueDeleted[] = $item;
            }
        }

        $this->info(sprintf('config/deleted.phpに保存する事業所数: %s', number_format(count($uniqueDeleted))));

        // config/deleted.phpを更新
        $this->updateDeletedConfig($uniqueDeleted);
        $this->info('config/deleted.phpを更新しました。');

        // --deleteオプションが指定されている場合は削除も実行
        if ($this->option('delete')) {
            $this->call('wam:delete');
        } else {
            $this->info('閉鎖済み事業所を削除するには `php artisan wam:delete` を実行してください。');
        }

        return 0;
    }

    /**
     * CSVからWAM NOと法人番号の組み合わせを取得
     */
    private function getAllCsvWamCompanyPairs(): array
    {
        $allPairs = [];
        $csvPath = resource_path('csv/'.config('wam.current'));
        $files = glob($csvPath.'/*.csv');

        foreach ($files as $file) {
            $this->comment(sprintf('処理中: %s', basename($file)));
            $handle = fopen($file, 'r');

            // ヘッダー行を読み込む
            $headers = fgetcsv($handle);
            $wamIndex = array_search('NO（※システム内の固有の番号、連番）', $headers);
            $companyIndex = array_search('法人番号', $headers);

            if ($wamIndex === false) {
                $this->warn(sprintf('ファイル %s に "NO（※システム内の固有の番号、連番）" 列が見つかりません', basename($file)));
                continue;
            }

            if ($companyIndex === false) {
                $this->warn(sprintf('ファイル %s に "法人番号" 列が見つかりません', basename($file)));
                continue;
            }

            while (($data = fgetcsv($handle)) !== false) {
                if (isset($data[$wamIndex]) && ! empty($data[$wamIndex]) &&
                    isset($data[$companyIndex]) && ! empty($data[$companyIndex])) {
                    $allPairs[] = $data[$wamIndex].'-'.$data[$companyIndex];
                }
            }

            fclose($handle);
        }

        return array_unique($allPairs);
    }

    private function updateDeletedConfig(array $deletedItems): void
    {
        usort($deletedItems, function ($a, $b) {
            $wamCompare = strcmp($a['wam'], $b['wam']);
            if ($wamCompare === 0) {
                return strcmp($a['company'], $b['company']);
            }

            return $wamCompare;
        });

        $content = "<?php\n\n/**\n * 閉鎖済み事業所。\n";
        $content .= " * 事業所番号は重複しているのでWAM ID「NO（※システム内の固有の番号、連番）」と「法人番号」のセットで指定。\n";
        $content .= " * 固有なはずのNOも重複しているので法人番号も必要。\n";
        $content .= " * ['wam' => 'A0000000000', 'company' => '1234567890123'],\n";
        $content .= " */\nreturn [\n";

        foreach ($deletedItems as $item) {
            $content .= "    ['wam' => '{$item['wam']}', 'company' => '{$item['company']}'],\n";
        }

        $content .= "];\n";

        file_put_contents(config_path('deleted.php'), $content);
    }
}
