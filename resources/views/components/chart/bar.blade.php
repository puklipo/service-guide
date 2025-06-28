@props(['data', 'labels', 'maxValue' => null, 'title' => null])

<x-chart.base :title="$title">
    @php
    // 最大値が指定されていなければ、データの最大値を使用
    $maxValue = $maxValue ?? max($data);

    // データとラベルをJSON形式にエンコード
    $jsonData = json_encode($data);
    $jsonLabels = json_encode($labels);
    @endphp

    <div class="chart-bar-component w-full">
        <script>
            // この即時実行関数は棒グラフを描画します
            (function() {
                const data = @json($data);
                const labels = @json($labels);
                const maxValue = @json($maxValue);

                // グラフのパラメータ
                const maxBarHeightPx = 256; // 最大の高さ（ピクセル）
                const barWidth = 32; // バーの幅
                const barSpacing = 8; // バー間のスペース
                const labelHeight = 48; // ラベルの高さ

                // 高さを計算
                function calculateBarHeight(value) {
                    return Math.round((value / maxValue) * maxBarHeightPx);
                }

                // DOMの準備ができたら実行
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.querySelector('.bar-chart-container');
                    if (!container) return;

                    // グラフ表示エリア
                    const barsContainer = document.createElement('div');
                    barsContainer.className = 'h-64 flex items-end justify-center space-x-1 md:space-x-2';

                    // ツールチップ
                    const tooltip = document.createElement('div');
                    tooltip.id = 'bar-chart-tooltip';
                    tooltip.className = 'mt-4 text-center text-sm text-gray-600 font-medium';
                    tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';

                    // 各バーを作成
                    data.forEach((value, index) => {
                        const barGroup = document.createElement('div');
                        barGroup.className = 'flex flex-col items-center';

                        // バー部分
                        const bar = document.createElement('div');
                        bar.className = 'w-8 md:w-12 bg-blue-500 rounded-t transition-all duration-200 cursor-pointer';
                        bar.style.height = `${calculateBarHeight(value)}px`;

                        // ホバー効果とツールチップの表示
                        bar.addEventListener('mouseenter', function() {
                            // バーのスタイル変更
                            bar.classList.add('bg-blue-600');

                            // ツールチップ
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}施設`;
                        });

                        bar.addEventListener('mouseleave', function() {
                            // バーのスタイル戻す
                            bar.classList.remove('bg-blue-600');

                            // ツールチップをリセット
                            tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                        });

                        // タッチデバイス対応
                        bar.addEventListener('touchstart', function() {
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}施設`;
                        });

                        // ラベル部分
                        const labelContainer = document.createElement('div');
                        labelContainer.className = 'text-xs md:text-sm text-gray-600 mt-2 h-12 flex items-center';

                        const label = document.createElement('span');
                        label.className = 'text-center';
                        label.textContent = labels[index];

                        labelContainer.appendChild(label);
                        barGroup.appendChild(bar);
                        barGroup.appendChild(labelContainer);
                        barsContainer.appendChild(barGroup);
                    });

                    // DOM追加
                    container.appendChild(barsContainer);
                    container.appendChild(tooltip);

                    // グリッド線の追加
                    const gridContainer = document.createElement('div');
                    gridContainer.className = 'absolute inset-0 pointer-events-none';
                    gridContainer.style.zIndex = '-1';

                    const gridCount = 4;
                    for (let i = 1; i <= gridCount; i++) {
                        const gridLine = document.createElement('div');
                        const position = 100 - (i / gridCount) * 100;
                        gridLine.className = 'absolute w-full h-px bg-gray-100';
                        gridLine.style.bottom = `${position}%`;
                        gridContainer.appendChild(gridLine);
                    }

                    container.style.position = 'relative';
                    container.appendChild(gridContainer);
                });
            })();
        </script>

        <!-- 棒グラフの表示エリア -->
        <div class="bar-chart-container w-full bg-white">
            <!-- グラフ要素はJavaScriptで動的に生成されます -->
        </div>
    </div>
</x-chart.base>
