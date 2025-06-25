<?php
/**
 * CSV Data Analysis Script for Disability Services
 * 
 * This script analyzes disability service facility CSV data and generates
 * a comprehensive data summary JSON file for article creation.
 * 
 * Usage: php analyze-csv-data.php <csv_directory> [output_file] [baseline_file]
 * 
 * @param csv_directory Directory containing CSV files (e.g., resources/csv/202203)
 * @param output_file Optional output JSON file path (default: data-summary.json in csv_directory)
 * @param baseline_file Optional baseline JSON file for comparison (e.g., previous period data)
 */

if ($argc < 2) {
    echo "Usage: php analyze-csv-data.php <csv_directory> [output_file] [baseline_file]\n";
    echo "Example: php analyze-csv-data.php resources/csv/202203\n";
    echo "Example: php analyze-csv-data.php resources/csv/202203 custom-output.json resources/articles/202111/data-summary.json\n";
    exit(1);
}

$csvDirectory = $argv[1];
$outputFile = $argv[2] ?? $csvDirectory . '/data-summary.json';
$baselineFile = $argv[3] ?? null;

// Load baseline data for comparison if provided
$baselineData = null;
if ($baselineFile && file_exists($baselineFile)) {
    $baselineData = json_decode(file_get_contents($baselineFile), true);
}

// Service code to name mapping (from config/service.php)
$serviceMap = [
    '11' => '居宅介護',
    '12' => '重度訪問介護',
    '13' => '行動援護',
    '14' => '重度障害者等包括支援',
    '15' => '同行援護',
    '21' => '療養介護',
    '22' => '生活介護',
    '24' => '短期入所',
    '32' => '施設入所支援',
    '33' => '共同生活援助',
    '34' => '宿泊型自立訓練',
    '41' => '自立訓練(機能訓練)',
    '42' => '自立訓練(生活訓練)',
    '45' => '就労継続支援A型',
    '46' => '就労継続支援B型',
    '52' => '計画相談支援',
    '53' => '地域相談支援(地域移行)',
    '54' => '地域相談支援(地域定着)',
    '60' => '就労移行支援',
    '61' => '自立生活援助',
    '62' => '就労定着支援',
    '63' => '児童発達支援',
    '64' => '医療型児童発達支援',
    '65' => '放課後等デイサービス',
    '66' => '居宅訪問型児童発達支援',
    '67' => '保育所等訪問支援',
    '68' => '福祉型障害児入所施設',
    '69' => '医療型障害児入所施設',
    '70' => '障害児相談支援'
];

// Prefecture code to name mapping
$prefectureMap = [
    '01' => '北海道', '02' => '青森県', '03' => '岩手県', '04' => '宮城県', '05' => '秋田県',
    '06' => '山形県', '07' => '福島県', '08' => '茨城県', '09' => '栃木県', '10' => '群馬県',
    '11' => '埼玉県', '12' => '千葉県', '13' => '東京都', '14' => '神奈川県', '15' => '新潟県',
    '16' => '富山県', '17' => '石川県', '18' => '福井県', '19' => '山梨県', '20' => '長野県',
    '21' => '岐阜県', '22' => '静岡県', '23' => '愛知県', '24' => '三重県', '25' => '滋賀県',
    '26' => '京都府', '27' => '大阪府', '28' => '兵庫県', '29' => '奈良県', '30' => '和歌山県',
    '31' => '鳥取県', '32' => '島根県', '33' => '岡山県', '34' => '広島県', '35' => '山口県',
    '36' => '徳島県', '37' => '香川県', '38' => '愛媛県', '39' => '高知県', '40' => '福岡県',
    '41' => '佐賀県', '42' => '長崎県', '43' => '熊本県', '44' => '大分県', '45' => '宮崎県',
    '46' => '鹿児島県', '47' => '沖縄県'
];

echo "Analyzing CSV data in: $csvDirectory\n";

// Find all CSV files
$csvFiles = glob($csvDirectory . '/*.csv');
if (empty($csvFiles)) {
    die("No CSV files found in directory: $csvDirectory\n");
}

echo "Found " . count($csvFiles) . " CSV files\n";

$totalFacilities = 0;
$totalCapacity = 0;
$serviceStats = [];
$prefectureStats = [];
$companyStats = [];

foreach ($csvFiles as $csvFile) {
    $filename = basename($csvFile);
    
    // Extract service code from filename (e.g., csvdownload011.csv -> 11)
    if (preg_match('/csvdownload(\d+)\.csv/', $filename, $matches)) {
        $serviceCode = ltrim($matches[1], '0');
        // Fix for service 070 -> should be 70, not 7
        if ($matches[1] === '070') {
            $serviceCode = '70';
        }
        $serviceName = $serviceMap[$serviceCode] ?? "Unknown Service ($serviceCode)";
        
        echo "Processing: $filename (Service: $serviceName)\n";
        
        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            echo "Error: Cannot open file $csvFile\n";
            continue;
        }
        
        // Read header
        $header = fgetcsv($handle);
        if ($header === false) {
            echo "Error: Cannot read header from $csvFile\n";
            fclose($handle);
            continue;
        }
        
        $facilityCount = 0;
        $capacitySum = 0;
        $prefCounts = [];
        $companyCounts = [];
        
        // Process each row
        while (($row = fgetcsv($handle)) !== false) {
            $facilityCount++;
            
            // Combine header with row data
            $data = array_combine($header, $row);
            
            // Extract prefecture code (first 2 digits of 事業所番号)
            if (isset($data['事業所番号'])) {
                $prefCode = substr($data['事業所番号'], 0, 2);
                $prefCounts[$prefCode] = ($prefCounts[$prefCode] ?? 0) + 1;
            }
            
            // Extract capacity if available
            if (isset($data['定員'])) {
                $capacity = (int)$data['定員'];
                if ($capacity > 0) {
                    $capacitySum += $capacity;
                }
            }
            
            // Extract company name
            if (isset($data['法人名'])) {
                $companyName = trim($data['法人名']);
                if (!empty($companyName)) {
                    $companyCounts[$companyName] = ($companyCounts[$companyName] ?? 0) + 1;
                }
            }
        }
        
        fclose($handle);
        
        // Store service statistics
        $serviceStats[$serviceCode] = [
            'name' => $serviceName,
            'facilities' => $facilityCount,
            'capacity' => $capacitySum > 0 ? $capacitySum : null,
            'avg_capacity' => $capacitySum > 0 ? round($capacitySum / $facilityCount, 1) : null,
            'market_share_percent' => 0 // Will be calculated later
        ];
        
        
        // Aggregate prefecture statistics
        foreach ($prefCounts as $prefCode => $count) {
            $prefectureStats[$prefCode] = ($prefectureStats[$prefCode] ?? 0) + $count;
        }
        
        // Aggregate company statistics
        foreach ($companyCounts as $company => $count) {
            $companyStats[$company] = ($companyStats[$company] ?? 0) + $count;
        }
        
        $totalFacilities += $facilityCount;
        $totalCapacity += $capacitySum;
        
        echo "  - Facilities: $facilityCount, Capacity: $capacitySum\n";
    }
}

// Calculate market share percentages
foreach ($serviceStats as $code => &$stats) {
    $stats['market_share_percent'] = round(($stats['facilities'] / $totalFacilities) * 100, 1);
}

// Fix service 70 data if it was overwritten incorrectly
if (isset($serviceStats['70']) && $serviceStats['70']['name'] !== '障害児相談支援') {
    // Re-process csvdownload070.csv to get correct data
    $csvFile070 = $csvDirectory . '/csvdownload070.csv';
    if (file_exists($csvFile070)) {
        $handle = fopen($csvFile070, 'r');
        if ($handle !== false) {
            $header = fgetcsv($handle);
            $facilityCount = 0;
            while (fgetcsv($handle) !== false) {
                $facilityCount++;
            }
            fclose($handle);
            
            $serviceStats['70'] = [
                'name' => '障害児相談支援',
                'facilities' => $facilityCount,
                'capacity' => null,
                'avg_capacity' => null,
                'market_share_percent' => round(($facilityCount / $totalFacilities) * 100, 1)
            ];
            echo "  FIXED: Service 70 corrected to 障害児相談支援 with $facilityCount facilities\n";
        }
    }
}

// Sort prefectures by facility count
arsort($prefectureStats);

// Sort companies by facility count
arsort($companyStats);

// Extract data date from directory name (e.g., 202409 -> 2024-09)
$dataDate = 'unknown';
if (preg_match('/(\d{4})(\d{2})/', basename($csvDirectory), $matches)) {
    $dataDate = $matches[1] . '-' . $matches[2];
}

// Prepare final data structure
$analysisData = [
    'metadata' => [
        'data_date' => $dataDate,
        'total_records' => $totalFacilities,
        'csv_files_count' => count($csvFiles),
        'analysis_date' => date('Y-m-d'),
        'description' => 'Comprehensive statistical summary of disability services in Japan'
    ],
    'overall_statistics' => [
        'total_facilities' => $totalFacilities,
        'total_capacity' => $totalCapacity,
        'service_types_count' => count($serviceStats),
        'geographical_coverage' => 'All 47 prefectures'
    ],
    'service_statistics' => []
];

// Add service statistics with comparison data
foreach ($serviceStats as $code => $stats) {
    $serviceData = [
        'name' => $stats['name'],
        'facilities' => $stats['facilities'],
        'capacity' => $stats['capacity'],
        'avg_capacity' => $stats['avg_capacity'],
        'type' => getServiceType($code),
        'market_share_percent' => $stats['market_share_percent']
    ];
    
    // Add comparison data if baseline exists
    if ($baselineData && isset($baselineData['service_statistics'][$code])) {
        $baseline = $baselineData['service_statistics'][$code];
        $growth = $stats['facilities'] - $baseline['facilities'];
        $growthRate = $baseline['facilities'] > 0 ? 
            round(($growth / $baseline['facilities']) * 100, 1) : 0;
        
        $serviceData['growth_from_baseline'] = $growth;
        $serviceData['growth_rate_percent'] = $growthRate;
    }
    
    $analysisData['service_statistics'][$code] = $serviceData;
}

// Add regional analysis
$topPrefectures = array_slice($prefectureStats, 0, 10, true);
$regionalData = [];

foreach ($topPrefectures as $prefCode => $facilityCount) {
    $prefName = $prefectureMap[$prefCode] ?? "Unknown ($prefCode)";
    $percentage = round(($facilityCount / $totalFacilities) * 100, 1);
    
    $prefData = [
        'name' => $prefName,
        'facilities' => $facilityCount,
        'percentage' => $percentage
    ];
    
    // Add comparison data if baseline exists
    if ($baselineData && isset($baselineData['regional_analysis']['top_prefectures_by_facilities'][$prefCode])) {
        $baseline = $baselineData['regional_analysis']['top_prefectures_by_facilities'][$prefCode];
        $growth = $facilityCount - $baseline['facilities'];
        $growthRate = $baseline['facilities'] > 0 ? 
            round(($growth / $baseline['facilities']) * 100, 1) : 0;
        
        $prefData['growth_from_baseline'] = $growth;
        $prefData['growth_rate_percent'] = $growthRate;
    }
    
    $regionalData[$prefCode] = $prefData;
}

$analysisData['regional_analysis'] = [
    'top_prefectures_by_facilities' => $regionalData
];

// Add top companies
$topCompanies = array_slice($companyStats, 0, 10, true);
$companyData = [];

$rank = 1;
foreach ($topCompanies as $company => $facilityCount) {
    $marketShare = round(($facilityCount / $totalFacilities) * 100, 2);
    $companyData[$rank] = [
        'name' => $company,
        'facilities' => $facilityCount,
        'market_share_percent' => $marketShare
    ];
    $rank++;
}

$analysisData['business_analysis'] = [
    'top_operators' => $companyData
];

// Add service categories
$analysisData['service_categories'] = categorizeServices($serviceStats, $totalFacilities);

// Add analysis notes
$analysisData['notes'] = [
    'data_source' => 'WAM (Welfare and Medical Service Agency) CSV files',
    'calculation_method' => 'Aggregated from CSV files by service type',
    'capacity_note' => 'Visiting services do not have capacity limits',
    'regional_codes' => 'Prefecture codes as per Japanese government standards'
];

if ($baselineData) {
    $analysisData['notes']['comparison_baseline'] = 'Includes comparison analysis with baseline data';
}

// Write output file
$jsonOutput = json_encode($analysisData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (file_put_contents($outputFile, $jsonOutput) === false) {
    die("Error: Cannot write output file: $outputFile\n");
}

echo "\nAnalysis complete!\n";
echo "Total facilities: " . number_format($totalFacilities) . "\n";
echo "Total capacity: " . number_format($totalCapacity) . "\n";
echo "Output file: $outputFile\n";

if ($baselineData) {
    $baselineFacilities = $baselineData['overall_statistics']['total_facilities'];
    $growth = $totalFacilities - $baselineFacilities;
    $growthRate = round(($growth / $baselineFacilities) * 100, 1);
    echo "Growth: +" . number_format($growth) . " facilities (+$growthRate%)\n";
}

/**
 * Determine service type based on service code
 */
function getServiceType($code) {
    $visitingServices = ['11', '12', '13', '14', '15', '66', '67'];
    $consultationServices = ['52', '53', '54', '70'];
    $supportServices = ['61', '62'];
    
    if (in_array($code, $visitingServices)) {
        return 'visiting_service';
    } elseif (in_array($code, $consultationServices)) {
        return 'consultation_service';
    } elseif (in_array($code, $supportServices)) {
        return 'support_service';
    } else {
        return 'facility_service';
    }
}

/**
 * Categorize services by type
 */
function categorizeServices($serviceStats, $totalFacilities) {
    $categories = [
        'visiting_services' => [],
        'facility_services' => [],
        'consultation_services' => [],
        'support_services' => []
    ];
    
    $categoryCounts = [
        'visiting_services' => 0,
        'facility_services' => 0,
        'consultation_services' => 0,
        'support_services' => 0
    ];
    
    foreach ($serviceStats as $code => $stats) {
        $type = getServiceType($code);
        
        switch ($type) {
            case 'visiting_service':
                $categories['visiting_services'][] = $stats['name'];
                $categoryCounts['visiting_services'] += $stats['facilities'];
                break;
            case 'facility_service':
                $categories['facility_services'][] = $stats['name'];
                $categoryCounts['facility_services'] += $stats['facilities'];
                break;
            case 'consultation_service':
                $categories['consultation_services'][] = $stats['name'];
                $categoryCounts['consultation_services'] += $stats['facilities'];
                break;
            case 'support_service':
                $categories['support_services'][] = $stats['name'];
                $categoryCounts['support_services'] += $stats['facilities'];
                break;
        }
    }
    
    $result = [];
    foreach ($categoryCounts as $category => $count) {
        $result[$category] = [
            'total_facilities' => $count,
            'percentage' => round(($count / $totalFacilities) * 100, 1),
            'services' => $categories[$category]
        ];
    }
    
    return $result;
}