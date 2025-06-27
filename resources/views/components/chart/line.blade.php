<x-chart.base>
    @props(['data', 'labels', 'maxValue' => null, 'title' => null])

    @php
        // 最大値が指定されていなければ、データの最大値を使用
        $maxValue = $maxValue ?? max($data);

        // データとラベルをJSON形式にエンコード
        $jsonData = json_encode($data);
        $jsonLabels = json_encode($labels);
    @endphp

    <!-- グラフデータをHTML属性として埋め込み（テスト用） -->
    <div
        class="chart-line-component w-full"
        data-chart-values="{{ $jsonData }}"
        data-chart-labels="{{ $jsonLabels }}"
        data-chart-max="{{ $maxValue }}"
    >
        <script type="application/json" id="chart-line-data">
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
                svgWidth: 1000,  // SVGの内部座標系の幅
                svgHeight: 400,  // SVGの内部座標系の高さ
                hoveredIndex: null,
                tooltip: 'グラフのポイントにカーソルを合わせると詳細が表示されます',

                // 点のX座標を計算
                getPointX(index) {
                    return (index / (this.chartData.length - 1)) * this.svgWidth;
                },

                // 点のY座標を計算
                getPointY(value) {
                    return this.svgHeight - ((value / this.maxValue) * this.svgHeight);
                },

                // 折れ線のパスを生成
                getPolylinePath() {
                    if (this.chartData.length === 0) return '';

                    return this.chartData.map((value, index) => {
                        const x = this.getPointX(index);
                        const y = this.getPointY(value);
                        return `${x},${y}`;
                    }).join(' ');
                },

                // ツールチップを更新
                showTooltip(index) {
                    this.hoveredIndex = index;
                    this.tooltip = `${this.chartLabels[index]}: ${this.chartData[index].toLocaleString()}施設`;
                },

                // ツールチップをリセット
                resetTooltip() {
                    this.hoveredIndex = null;
                    this.tooltip = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                }
            }"
        >
            <div class="h-64 w-full">
                <svg class="w-full h-full" viewBox="0 0 1000 400" preserveAspectRatio="none">
                    <!-- 折れ線 -->
                    <polyline
                        :points="getPolylinePath()"
                        fill="none"
                        stroke="#3b82f6"  <!-- 固定の青色 -->
                        stroke-width="3"
                        vector-effect="non-scaling-stroke"
                    />

                    <!-- データポイント -->
                    <template x-for="(value, index) in chartData" :key="index">
                        <circle
                            :cx="getPointX(index)"
                            :cy="getPointY(value)"
                            r="6"
                            :class="hoveredIndex === index ? 'fill-blue-700' : 'fill-blue-500'"
                            @mouseover="showTooltip(index)"
                            @mouseout="resetTooltip()"
                        />
                    </template>
                </svg>
            </div>

            <!-- X軸ラベル -->
            <div class="flex justify-between mt-2">
                <template x-for="(label, index) in chartLabels" :key="index">
                    <div class="text-xs whitespace-nowrap" x-text="label.substring(0,7)"></div>
                </template>
            </div>

            <!-- ツールチップ -->
            <div class="text-sm mt-4 text-center font-medium" x-text="tooltip"></div>
        </div>
    </div>
</x-chart.base>
