<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DetectDuplicatesCommand extends Command
{
    protected $signature = 'wam:detect-duplicates';

    protected $description = 'CSVファイル内の事業所番号とWAM NOの重複を検出して報告します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // CSVから全事業所番号とWAM NOを抽出
        $this->info('CSVから事業所番号とWAM NOを収集中...');
        $result = $this->getAllCsvData();

        $numbers = $result['numbers'];
        $wamIds = $result['wamIds'];

        $this->info(sprintf('CSV内の事業所数: %s', number_format(count($numbers))));

        // 事業所番号の重複を調査
        $this->analyzeDuplicates($numbers, '事業所番号');

        // WAM NOの重複を調査
        $this->analyzeDuplicates($wamIds, 'WAM NO');

        return 0;
    }

    /**
     * CSVから全事業所番号とWAM NOを取得
     */
    private function getAllCsvData(): array
    {
        $allNumbers = [];
        $allWamIds = [];
        $csvPath = resource_path('csv/'.config('wam.current'));
        $files = glob($csvPath.'/*.csv');

        foreach ($files as $file) {
            $this->comment(sprintf('処理中: %s', basename($file)));
            $handle = fopen($file, 'r');

            // ヘッダー行を読み込む
            $headers = fgetcsv($handle);
            $noIndex = array_search('事業所番号', $headers);
            $wamIndex = array_search('NO（※システム内の固有の番号、連番）', $headers);

            if ($noIndex === false) {
                $this->warn(sprintf('ファイル %s に "事業所番号" 列が見つかりません', basename($file)));

                continue;
            }

            if ($wamIndex === false) {
                $this->warn(sprintf('ファイル %s に "NO（※システム内の固有の番号、連番）" 列が見つかりません', basename($file)));

                continue;
            }

            while (($data = fgetcsv($handle)) !== false) {
                if (isset($data[$noIndex]) && is_numeric($data[$noIndex])) {
                    $allNumbers[] = (string) $data[$noIndex];
                }

                if (isset($data[$wamIndex]) && ! empty($data[$wamIndex])) {
                    $allWamIds[] = (string) $data[$wamIndex];
                }
            }

            fclose($handle);
        }

        return [
            'numbers' => array_filter($allNumbers),
            'wamIds' => array_filter($allWamIds),
        ];
    }

    /**
     * 重複を分析して結果を表示
     */
    private function analyzeDuplicates(array $items, string $itemName): void
    {
        $this->info(sprintf("\n%s の重複分析:", $itemName));

        // 全アイテム数
        $totalItems = count($items);
        $this->info(sprintf('総%s数: %s', $itemName, number_format($totalItems)));

        // ユニークなアイテム数
        $uniqueItems = array_unique($items);
        $uniqueCount = count($uniqueItems);
        $this->info(sprintf('ユニークな%s数: %s', $itemName, number_format($uniqueCount)));

        // 重複している数
        $duplicatesCount = $totalItems - $uniqueCount;
        $this->info(sprintf('重複している%s数: %s (%.2f%%)',
            $itemName,
            number_format($duplicatesCount),
            ($duplicatesCount / $totalItems) * 100,
        ));

        // 重複回数の分析
        $frequency = array_count_values($items);
        arsort($frequency);

        // 最も重複しているアイテム
        $this->info("\n最も重複している{$itemName}:");
        $this->table(
            ["{$itemName}", '出現回数'],
            $this->getTopDuplicates($frequency, 10),
        );

        // 重複頻度の分布
        $this->info("\n重複頻度の分布:");
        $distribution = $this->getDuplicateDistribution($frequency);
        $this->table(
            ['出現回数', "{$itemName}の数"],
            $distribution,
        );
    }

    /**
     * 最も重複している項目を取得
     */
    private function getTopDuplicates(array $frequency, int $limit): array
    {
        $result = [];
        $count = 0;

        foreach ($frequency as $item => $freq) {
            if ($freq > 1) {
                $result[] = [$item, $freq];
                $count++;

                if ($count >= $limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * 重複頻度の分布を取得
     */
    private function getDuplicateDistribution(array $frequency): array
    {
        $distribution = [];
        $countByFrequency = [];

        foreach ($frequency as $freq) {
            if (! isset($countByFrequency[$freq])) {
                $countByFrequency[$freq] = 0;
            }
            $countByFrequency[$freq]++;
        }

        ksort($countByFrequency);

        foreach ($countByFrequency as $freq => $count) {
            $distribution[] = [$freq, $count];
        }

        return $distribution;
    }
}
