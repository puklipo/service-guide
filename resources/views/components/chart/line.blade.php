@props(['data', 'labels', 'maxValue' => null, 'title' => null])

<x-chart.base :title="$title">
    @php
    // 最大値が指定されていなければ、データの最大値に2%余裕を持たせる
    $dataMaxValue = max($data);
    $maxValue = $maxValue ?? round($dataMaxValue * 1.02);

    // 最小値を取得
    $minValue = min($data);
    // データの範囲を計算
    $dataRange = $maxValue - $minValue;

    // 表示最小値: 実際の最小値より10%下げる（データ範囲の10%分）
    $displayMinValue = $minValue - ($dataRange * 0.1);
    // 下限値が負になる場合は0に補正
    $displayMinValue = max(0, $displayMinValue);

    // 調整後のデータ範囲
    $adjustedDataRange = $maxValue - $displayMinValue;
    @endphp

    <div class="chart-line-component w-full" x-data="chartLine({
        data: {{ json_encode($data) }},
        labels: {{ json_encode($labels) }},
        maxValue: {{ json_encode($maxValue) }},
        displayMinValue: {{ json_encode($displayMinValue) }},
        adjustedDataRange: {{ json_encode($adjustedDataRange) }}
    })" x-init="init()">
        <div x-ref="container" class="line-chart-container w-full bg-white dark:bg-gray-900 rounded-md">
            <!-- Alpine will generate the chart here -->
        </div>
    </div>
</x-chart.base>
