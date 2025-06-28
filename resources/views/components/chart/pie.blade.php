@props(['data', 'labels', 'title' => null])

<x-chart.base :title="$title">
    @php
    // 合計値を計算
    $total = array_sum($data);

    // 固定の色リスト - Tailwindの色に合わせる
    $colors = [
        '#3b82f6', // blue-500
        '#10b981', // green-500
        '#ef4444', // red-500
        '#f59e0b', // amber-500
        '#8b5cf6', // violet-500
        '#ec4899', // pink-500
        '#6b7280', // gray-500
        '#0ea5e9'  // sky-500
    ];
    @endphp

    <div class="chart-pie-component w-full" x-data="chartPie({
        data: {{ json_encode($data) }},
        labels: {{ json_encode($labels) }},
        total: {{ $total }},
        colors: {{ json_encode($colors) }}
    })" x-init="init()">
        <div class="flex flex-col items-center">
            <div x-ref="container" class="pie-chart-container w-64 h-64 mx-auto bg-white dark:bg-gray-900">
                <!-- Alpine will generate the chart here -->
            </div>

            <div x-ref="tooltip" class="text-center text-sm text-gray-600 dark:text-gray-300 mt-3 font-medium">
                円グラフのセグメントにカーソルを合わせると詳細が表示されます
            </div>

            <div x-ref="legendContainer" class="pie-chart-legend mt-4 w-full bg-white dark:bg-gray-900">
                <!-- Alpine will generate the legend here -->
            </div>
        </div>
    </div>
</x-chart.base>
