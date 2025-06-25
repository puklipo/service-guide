<?php

/**
 * サマリーJSON生成スクリプト
 *
 * 使用法: php scripts/generate-summary-json.php 202203 202111
 *
 * @param  string  $targetPeriod  対象期間 (例: 202203)
 * @param  string  $comparisonPeriod  比較期間 (例: 202111)
 */

// 引数のチェック
if ($argc < 2 || $argc > 3) {
    echo "使用法: php scripts/generate-summary-json.php <対象期間> [比較期間]\n";
    echo "例: php scripts/generate-summary-json.php 202203 202111\n";
    echo "例: php scripts/generate-summary-json.php 202111 (比較なし)\n";
    exit(1);
}

$targetPeriod = $argv[1];
$comparisonPeriod = isset($argv[2]) ? $argv[2] : null;

// パスの設定
$basePath = __DIR__.'/..';
$targetCsvPath = $basePath.'/resources/csv/'.$targetPeriod;
$comparisonCsvPath = $basePath.'/resources/csv/'.$comparisonPeriod;
$outputPath = $basePath.'/resources/articles/'.$targetPeriod.'/data-summary.json';
$configPath = $basePath.'/config/service.php';

// ディレクトリの存在確認
if (! is_dir($targetCsvPath)) {
    echo "エラー: 対象期間のCSVディレクトリが存在しません: $targetCsvPath\n";
    exit(1);
}

if ($comparisonPeriod && ! is_dir($comparisonCsvPath)) {
    echo "エラー: 比較期間のCSVディレクトリが存在しません: $comparisonCsvPath\n";
    exit(1);
}

// 出力ディレクトリの作成
$outputDir = dirname($outputPath);
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// サービス設定の読み込み
if (! file_exists($configPath)) {
    echo "エラー: サービス設定ファイルが存在しません: $configPath\n";
    exit(1);
}

$serviceConfig = include $configPath;

/**
 * サービスタイプの分類
 */
function getServiceType($serviceCode)
{
    $visitingServices = [11, 12, 13, 14, 15, 66, 67];
    $facilityServices = [21, 22, 24, 32, 33, 34, 41, 42, 45, 46, 60, 63, 64, 65, 68, 69];
    $consultationServices = [52, 53, 54, 70];
    $supportServices = [61, 62];

    if (in_array($serviceCode, $visitingServices)) {
        return 'visiting_service';
    }
    if (in_array($serviceCode, $facilityServices)) {
        return 'facility_service';
    }
    if (in_array($serviceCode, $consultationServices)) {
        return 'consultation_service';
    }
    if (in_array($serviceCode, $supportServices)) {
        return 'support_service';
    }

    return 'other';
}

/**
 * CSVファイルを読み込んで統計データを生成
 */
function analyzeCSVData($csvPath, $serviceConfig)
{
    $statistics = [
        'total_facilities' => 0,
        'total_capacity' => 0,
        'service_stats' => [],
        'regional_stats' => [],
        'corporation_stats' => [],
        'capacity_stats' => [],
        'csv_files_processed' => 0,
    ];

    // サービス統計の初期化
    foreach ($serviceConfig as $code => $name) {
        $statistics['service_stats'][$code] = [
            'name' => $name,
            'facilities' => 0,
            'capacity' => 0,
            'type' => getServiceType($code),
        ];
    }

    // 都道府県マスター
    $prefectures = [
        '01' => '北海道', '02' => '青森県', '03' => '岩手県', '04' => '宮城県', '05' => '秋田県',
        '06' => '山形県', '07' => '福島県', '08' => '茨城県', '09' => '栃木県', '10' => '群馬県',
        '11' => '埼玉県', '12' => '千葉県', '13' => '東京都', '14' => '神奈川県', '15' => '新潟県',
        '16' => '富山県', '17' => '石川県', '18' => '福井県', '19' => '山梨県', '20' => '長野県',
        '21' => '岐阜県', '22' => '静岡県', '23' => '愛知県', '24' => '三重県', '25' => '滋賀県',
        '26' => '京都府', '27' => '大阪府', '28' => '兵庫県', '29' => '奈良県', '30' => '和歌山県',
        '31' => '鳥取県', '32' => '島根県', '33' => '岡山県', '34' => '広島県', '35' => '山口県',
        '36' => '徳島県', '37' => '香川県', '38' => '愛媛県', '39' => '高知県', '40' => '福岡県',
        '41' => '佐賀県', '42' => '長崎県', '43' => '熊本県', '44' => '大分県', '45' => '宮崎県',
        '46' => '鹿児島県', '47' => '沖縄県',
    ];

    // CSVファイルの処理
    $csvFiles = glob($csvPath.'/csvdownload*.csv');

    foreach ($csvFiles as $csvFile) {
        echo '処理中: '.basename($csvFile)."\n";

        if (($handle = fopen($csvFile, 'r')) !== false) {
            // ヘッダーをスキップ
            fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 29) {
                    continue;
                }

                $prefCode = substr($data[0], 0, 2); // 都道府県コード
                $corporationName = trim($data[3]); // 法人名
                $serviceType = trim($data[11]); // サービス種別
                $capacity = isset($data[28]) && $data[28] !== '' ? intval($data[28]) : 0;

                // サービス種別でコードを特定
                $serviceCode = null;
                foreach ($serviceConfig as $code => $name) {
                    if ($name === $serviceType) {
                        $serviceCode = $code;
                        break;
                    }
                }

                if ($serviceCode !== null) {
                    // サービス統計
                    $statistics['service_stats'][$serviceCode]['facilities']++;
                    $statistics['service_stats'][$serviceCode]['capacity'] += $capacity;
                    $statistics['total_facilities']++;
                    $statistics['total_capacity'] += $capacity;

                    // 地域統計
                    if (! isset($statistics['regional_stats'][$prefCode])) {
                        $statistics['regional_stats'][$prefCode] = [
                            'name' => $prefectures[$prefCode] ?? '不明',
                            'facilities' => 0,
                        ];
                    }
                    $statistics['regional_stats'][$prefCode]['facilities']++;

                    // 法人形態分析
                    $corpType = 'others';
                    if (strpos($corporationName, '社会福祉法人') !== false) {
                        $corpType = 'social_welfare';
                    } elseif (strpos($corporationName, '株式会社') !== false) {
                        $corpType = 'joint_stock';
                    } elseif (strpos($corporationName, 'NPO') !== false || strpos($corporationName, '特定非営利活動法人') !== false) {
                        $corpType = 'npo';
                    }

                    if (! isset($statistics['corporation_stats'][$corpType])) {
                        $statistics['corporation_stats'][$corpType] = ['facilities' => 0, 'capacity' => 0];
                    }
                    $statistics['corporation_stats'][$corpType]['facilities']++;
                    $statistics['corporation_stats'][$corpType]['capacity'] += $capacity;

                    // 定員規模分析
                    if ($capacity > 0) {
                        $sizeCategory = 'small';
                        if ($capacity >= 30) {
                            $sizeCategory = 'large';
                        } elseif ($capacity >= 11) {
                            $sizeCategory = 'medium';
                        }

                        if (! isset($statistics['capacity_stats'][$sizeCategory])) {
                            $statistics['capacity_stats'][$sizeCategory] = ['facilities' => 0, 'capacity' => 0];
                        }
                        $statistics['capacity_stats'][$sizeCategory]['facilities']++;
                        $statistics['capacity_stats'][$sizeCategory]['capacity'] += $capacity;
                    }
                }
            }

            fclose($handle);
            $statistics['csv_files_processed']++;
        }
    }

    // 各種統計の計算
    // 市場シェアと平均定員の計算
    foreach ($statistics['service_stats'] as $code => &$stat) {
        if ($statistics['total_facilities'] > 0) {
            $stat['market_share_percent'] = round(($stat['facilities'] / $statistics['total_facilities']) * 100, 1);
        } else {
            $stat['market_share_percent'] = 0;
        }

        if ($stat['facilities'] > 0 && $stat['capacity'] > 0) {
            $stat['avg_capacity'] = round($stat['capacity'] / $stat['facilities'], 1);
        }
    }

    // 地域統計のソート
    uasort($statistics['regional_stats'], function ($a, $b) {
        return $b['facilities'] - $a['facilities'];
    });

    return $statistics;
}

echo "対象期間 ($targetPeriod) の分析開始...\n";
$targetStats = analyzeCSVData($targetCsvPath, $serviceConfig);

$comparisonStats = null;
if ($comparisonPeriod) {
    echo "比較期間 ($comparisonPeriod) の分析開始...\n";
    $comparisonStats = analyzeCSVData($comparisonCsvPath, $serviceConfig);
}

// 比較データの計算
$overallGrowth = null;
$overallGrowthRate = null;
$capacityGrowth = null;
$capacityGrowthRate = null;

if ($comparisonStats) {
    $overallGrowth = $targetStats['total_facilities'] - $comparisonStats['total_facilities'];
    $overallGrowthRate = $comparisonStats['total_facilities'] > 0
        ? round(($overallGrowth / $comparisonStats['total_facilities']) * 100, 1)
        : 0;

    $capacityGrowth = $targetStats['total_capacity'] - $comparisonStats['total_capacity'];
    $capacityGrowthRate = $comparisonStats['total_capacity'] > 0
        ? round(($capacityGrowth / $comparisonStats['total_capacity']) * 100, 1)
        : 0;
}

// サービス別比較統計
$serviceStatistics = [];
foreach ($targetStats['service_stats'] as $code => $stat) {
    $serviceData = [
        'name' => $stat['name'],
        'facilities' => $stat['facilities'],
        'capacity' => $stat['capacity'] > 0 ? $stat['capacity'] : null,
        'type' => $stat['type'],
        'market_share_percent' => $stat['market_share_percent'],
    ];

    if (isset($stat['avg_capacity'])) {
        $serviceData['avg_capacity'] = $stat['avg_capacity'];
    }

    if ($comparisonStats) {
        $previousFacilities = $comparisonStats['service_stats'][$code]['facilities'] ?? 0;
        $growth = $stat['facilities'] - $previousFacilities;
        $growthRate = $previousFacilities > 0 ? round(($growth / $previousFacilities) * 100, 1) : 0;

        $serviceData['previous_facilities'] = $previousFacilities;
        $serviceData['growth'] = $growth;
        $serviceData['growth_rate_percent'] = $growthRate;
    }

    $serviceStatistics[$code] = $serviceData;
}

// 地域分析データの構築
$regionalAnalysis = [];

// 上位5都道府県
$topPrefectures = [];
$count = 0;
foreach ($targetStats['regional_stats'] as $prefCode => $data) {
    if ($count >= 5) {
        break;
    }
    $topPrefectures[$prefCode] = [
        'name' => $data['name'],
        'facilities' => $data['facilities'],
        'percentage' => round(($data['facilities'] / $targetStats['total_facilities']) * 100, 1),
    ];
    $count++;
}
$regionalAnalysis['top_prefectures_by_facilities'] = $topPrefectures;

// 首都圏分析
$metropolitanCodes = ['13', '14', '11', '12', '27', '28', '26']; // 東京、神奈川、埼玉、千葉、大阪、兵庫、京都
$metropolitanFacilities = 0;
foreach ($metropolitanCodes as $code) {
    $metropolitanFacilities += $targetStats['regional_stats'][$code]['facilities'] ?? 0;
}
$regionalAnalysis['regional_distribution'] = [
    'metropolitan_areas' => [
        'facilities' => $metropolitanFacilities,
        'percentage' => round(($metropolitanFacilities / $targetStats['total_facilities']) * 100, 1),
        'prefectures' => ['東京都', '神奈川県', '埼玉県', '千葉県', '大阪府', '兵庫県', '京都府'],
    ],
    'local_areas' => [
        'facilities' => $targetStats['total_facilities'] - $metropolitanFacilities,
        'percentage' => round((($targetStats['total_facilities'] - $metropolitanFacilities) / $targetStats['total_facilities']) * 100, 1),
        'characteristics' => 'Higher service density per capita in rural areas',
    ],
];

// 事業者分析
$businessAnalysis = [];
$corporationTypes = [
    'social_welfare' => ['name' => '社会福祉法人', 'characteristics' => 'Larger facilities, traditional services'],
    'joint_stock' => ['name' => '株式会社', 'characteristics' => 'Smaller facilities, visiting services'],
    'npo' => ['name' => 'NPO法人', 'characteristics' => 'Community-based services'],
    'others' => ['name' => 'その他', 'characteristics' => 'Including medical corporations, government entities'],
];

foreach ($corporationTypes as $type => $info) {
    if (isset($targetStats['corporation_stats'][$type])) {
        $facilities = $targetStats['corporation_stats'][$type]['facilities'];
        $capacity = $targetStats['corporation_stats'][$type]['capacity'];

        $businessAnalysis['corporation_types'][$type] = [
            'facilities' => $facilities,
            'percentage' => round(($facilities / $targetStats['total_facilities']) * 100, 1),
            'avg_capacity' => $facilities > 0 && $capacity > 0 ? round($capacity / $facilities, 1) : 0,
            'characteristics' => $info['characteristics'],
        ];
    }
}

// 定員規模分析
$capacityAnalysis = [];
$sizeCategories = [
    'large' => ['definition' => '30+ capacity'],
    'medium' => ['definition' => '11-30 capacity'],
    'small' => ['definition' => '1-10 capacity'],
];

$totalWithCapacity = 0;
foreach ($targetStats['capacity_stats'] as $category => $data) {
    $totalWithCapacity += $data['facilities'];
}

foreach ($sizeCategories as $category => $info) {
    if (isset($targetStats['capacity_stats'][$category])) {
        $facilities = $targetStats['capacity_stats'][$category]['facilities'];
        $capacity = $targetStats['capacity_stats'][$category]['capacity'];

        $capacityAnalysis[$category.'_facilities'] = [
            'definition' => $info['definition'],
            'count' => $facilities,
            'percentage' => $totalWithCapacity > 0 ? round(($facilities / $totalWithCapacity) * 100, 1) : 0,
            'avg_capacity' => $facilities > 0 ? round($capacity / $facilities, 1) : 0,
        ];
    }
}
$businessAnalysis['capacity_analysis'] = $capacityAnalysis;

// サービスカテゴリー別統計
$serviceCategories = [];
$categoryTypes = [
    'visiting_services' => [11, 12, 13, 14, 15, 66, 67],
    'facility_services' => [21, 22, 24, 32, 33, 34, 41, 42, 45, 46, 60, 63, 64, 65, 68, 69],
    'consultation_services' => [52, 53, 54, 70],
    'support_services' => [61, 62],
];

foreach ($categoryTypes as $categoryName => $serviceCodes) {
    $totalFacilities = 0;
    $totalCapacity = 0;
    $serviceNames = [];

    foreach ($serviceCodes as $code) {
        if (isset($targetStats['service_stats'][$code])) {
            $totalFacilities += $targetStats['service_stats'][$code]['facilities'];
            $totalCapacity += $targetStats['service_stats'][$code]['capacity'];
            $serviceNames[] = $targetStats['service_stats'][$code]['name'];
        }
    }

    $serviceCategories[$categoryName] = [
        'total_facilities' => $totalFacilities,
        'percentage' => round(($totalFacilities / $targetStats['total_facilities']) * 100, 1),
        'services' => $serviceNames,
    ];

    if ($totalCapacity > 0) {
        $serviceCategories[$categoryName]['total_capacity'] = $totalCapacity;
    }
}

// 結果JSONの構築
$metadata = [
    'data_date' => substr($targetPeriod, 0, 4).'-'.substr($targetPeriod, 4, 2), // 202111 -> 2021-11 形式に
    'total_records' => $targetStats['total_facilities'],
    'csv_files_count' => $targetStats['csv_files_processed'],
    'analysis_date' => date('Y-m-d'),
    'description' => 'Comprehensive statistical summary of disability services in Japan as of '.
                    substr($targetPeriod, 0, 4).'-'.substr($targetPeriod, 4, 2),
];

$overallStatistics = [
    'total_facilities' => $targetStats['total_facilities'],
    'total_capacity' => $targetStats['total_capacity'],
    'service_types_count' => count($serviceConfig),
    'geographical_coverage' => 'All 47 prefectures',
];

if ($comparisonPeriod) {
    $metadata['comparison_date'] = substr($comparisonPeriod, 0, 4).'-'.substr($comparisonPeriod, 4, 2);
    $overallStatistics['previous_facilities'] = $comparisonStats['total_facilities'];
    $overallStatistics['previous_capacity'] = $comparisonStats['total_capacity'];
    $overallStatistics['facility_growth'] = $overallGrowth;
    $overallStatistics['facility_growth_rate_percent'] = $overallGrowthRate;
    $overallStatistics['capacity_growth'] = $capacityGrowth;
    $overallStatistics['capacity_growth_rate_percent'] = $capacityGrowthRate;
}

// 市場洞察の生成
$marketInsights = [
    'growth_services' => [],
    'mature_services' => [],
    'specialized_services' => [],
];

// 成長サービス（児童系、就労支援系）
$growthServiceCodes = [65, 63, 62, 61]; // 放課後等デイ、児童発達支援、就労定着支援、自立生活援助
foreach ($growthServiceCodes as $code) {
    if (isset($targetStats['service_stats'][$code])) {
        $marketInsights['growth_services'][] = $targetStats['service_stats'][$code]['name'];
    }
}

// 成熟サービス（大手のサービス）
$matureServiceCodes = [11, 12, 22, 46]; // 居宅介護、重度訪問介護、生活介護、就労継続支援B型
foreach ($matureServiceCodes as $code) {
    if (isset($targetStats['service_stats'][$code])) {
        $marketInsights['mature_services'][] = $targetStats['service_stats'][$code]['name'];
    }
}

// 専門性の高いサービス（施設数が少ないもの）
$specializedServiceCodes = [14, 64, 69]; // 重度障害者等包括支援、医療型児童発達支援、医療型障害児入所施設
foreach ($specializedServiceCodes as $code) {
    if (isset($targetStats['service_stats'][$code])) {
        $marketInsights['specialized_services'][] = $targetStats['service_stats'][$code]['name'];
    }
}

$result = [
    'metadata' => $metadata,
    'overall_statistics' => $overallStatistics,
    'service_statistics' => $serviceStatistics,
    'regional_analysis' => $regionalAnalysis,
    'business_analysis' => $businessAnalysis,
    'service_categories' => $serviceCategories,
    'market_insights' => $marketInsights,
    'notes' => [
        'data_source' => 'WAM (Welfare and Medical Service Agency) CSV files',
        'calculation_method' => 'Aggregated from '.$targetStats['csv_files_processed'].' CSV files by service type',
        'capacity_note' => 'Visiting services do not have capacity limits',
        'regional_codes' => 'Prefecture codes as per Japanese government standards',
    ],
];

// JSONファイルの出力
$jsonOutput = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (file_put_contents($outputPath, $jsonOutput)) {
    echo "サマリーJSONを生成しました: $outputPath\n";
    echo "総施設数: {$targetStats['total_facilities']}\n";
    echo "総定員数: {$targetStats['total_capacity']}\n";

    if ($comparisonPeriod && $overallGrowth !== null) {
        echo '前回比: 施設数 '.sprintf('%+d', $overallGrowth).' ('.sprintf('%+.1f', $overallGrowthRate).'%), ';
        echo '定員数 '.sprintf('%+d', $capacityGrowth).' ('.sprintf('%+.1f', $capacityGrowthRate)."%)\n";
    }
} else {
    echo "エラー: JSONファイルの書き込みに失敗しました: $outputPath\n";
    exit(1);
}
