@props(['data', 'labels', 'maxValue' => null, 'title' => null])

<x-chart.base :title="$title">
    @php
    // 最大値が指定されていなければ、データの最大値を使用
    $maxValue = $maxValue ?? max($data);

    // データとラベルをJSON形式にエンコード
    $jsonData = json_encode($data);
    $jsonLabels = json_encode($labels);
    @endphp

    <!-- グラフデータをHTML属性として埋め込み（テスト用） -->
    <div
        class="chart-bar-component w-full"
        data-chart-values="{{ $jsonData }}"
        data-chart-labels="{{ $jsonLabels }}"
        data-chart-max="{{ $maxValue }}"
    >
        <script type="application/json" id="chart-data">
            {
                "data": {{ $jsonData }},
                "labels": {{ $jsonLabels }},
                "maxValue": {{ $maxValue }}
            }
        </script>

        <!-- Alpine.jsコンポーネント -->
        <div
            x-data="{
                chartData: {{ $jsonData }},
                chartLabels: {{ $jsonLabels }},
                maxValue: {{ $maxValue }},
                maxBarHeightPx: 256, // 最大の高さ（ピクセル）
                tooltip: '詳細を表示するにはグラフにカーソルを合わせてください',

                // 高さをピクセル単位で計算（パーセント指定ではなく）
                getBarHeight(value) {
                    const heightPx = Math.round((value / this.maxValue) * this.maxBarHeightPx);
                    return `${heightPx}px`;
                },

                showTooltip(index) {
                    this.tooltip = `${this.chartLabels[index]}: ${this.chartData[index].toLocaleString()}施設`;
                }
            }"
        >
            <div class="h-64 flex items-end justify-center space-x-1 md:space-x-2">
                <template x-for="(value, index) in chartData" :key="index">
                    <div class="flex flex-col items-center">
                        <!-- バー -->
                        <div
                            class="w-8 md:w-12 bg-blue-500 hover:bg-blue-700 transition-all cursor-pointer"
                            :style="`height: ${getBarHeight(value)}`"
                            @mouseover="showTooltip(index)"
                            :data-value="value"
                        ></div>

                        <!-- ラベル -->
                        <div class="text-xs mt-2 transform rotate-45 origin-left whitespace-nowrap"
                             x-text="chartLabels[index].substring(0, 7)"></div>
                    </div>
                </template>
            </div>

            <!-- ツールチップ -->
            <div class="mt-8 text-center text-sm font-medium" x-text="tooltip"></div>
        </div>
    </div>
</x-chart.base>
