@props(['data', 'labels', 'maxValue' => null, 'title' => null])

<x-chart.base :title="$title">
    @php
    // 最大値が指定されていなければ、データの最大値に10%余裕を持たせる
    $dataMaxValue = max($data);
    $maxValue = $maxValue ?? round($dataMaxValue * 1.1);

    // 最小値を取得
    $minValue = min($data);
    // データ範囲を計算
    $dataRange = $maxValue - $minValue;

    // 表示最小値: 実際の最小値より10%上げる（ただしデータ範囲の10%分）
    $displayMinValue = $minValue - ($dataRange * 0.1);
    // 下限値が負になる場合は0に補正
    $displayMinValue = max(0, $displayMinValue);

    // 調整後のデータ範囲
    $adjustedDataRange = $maxValue - $displayMinValue;

    // データとラベルをJSON形式にエンコード
    $jsonData = json_encode($data);
    $jsonLabels = json_encode($labels);
    $jsonMinValue = json_encode($minValue);
    $jsonDisplayMinValue = json_encode($displayMinValue);
    @endphp

    <div class="chart-bar-component w-full">
        <script>
            // この即時実行関数は棒グラフを描画します
            (function() {
                const data = @json($data);
                const labels = @json($labels);
                const maxValue = @json($maxValue);
                const minValue = @json($minValue);
                const displayMinValue = @json($displayMinValue);
                const adjustedDataRange = @json($adjustedDataRange);

                // ダークモードかどうかを検出
                const isDarkMode = () => {
                    return document.documentElement.classList.contains('dark') ||
                           window.matchMedia('(prefers-color-scheme: dark)').matches;
                };

                // グラフのパラメータ
                const maxBarHeightPx = 256; // 最大の高さ（ピクセル）
                const barWidth = 32; // バーの幅
                const barSpacing = 8; // バー間のスペース
                const labelHeight = 48; // ラベルの高さ

                // 高さを計算（調整後の最小値を考慮）
                function calculateBarHeight(value) {
                    // 値の相対位置に基づいて高さを計算（調整後の最小値からの相対的な高さ）
                    return Math.round(((value - displayMinValue) / adjustedDataRange) * maxBarHeightPx);
                }

                // DOMの準備ができたら実行
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.querySelector('.bar-chart-container');
                    if (!container) return;

                    // ダークモードの監視
                    const darkModeObserver = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'class') {
                                updateDarkModeStyles();
                            }
                        });
                    });

                    darkModeObserver.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });

                    // グラフ表示エリア
                    const barsContainer = document.createElement('div');
                    barsContainer.className = 'h-64 flex items-end justify-center space-x-1 md:space-x-2';

                    // ツールチップ
                    const tooltip = document.createElement('div');
                    tooltip.id = 'bar-chart-tooltip';
                    tooltip.className = 'mt-4 text-center text-sm font-medium';
                    tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';

                    // 表示最小値のベースラインを表示
                    const baselineInfo = document.createElement('div');
                    baselineInfo.className = 'text-xs text-right w-full pr-2 opacity-70 -mb-1';
                    baselineInfo.textContent = `表示最小値: ${displayMinValue.toLocaleString()}`;
                    container.appendChild(baselineInfo);

                    // 表示最大値の情報を右上に表示
                    const maxValueInfo = document.createElement('div');
                    maxValueInfo.className = 'text-xs text-right w-full pr-2 opacity-70';
                    maxValueInfo.textContent = `表示最大値: ${maxValue.toLocaleString()}`;
                    container.appendChild(maxValueInfo);

                    // バーの配列を保持（ダークモード切り替え時に参照するため）
                    const barElements = [];
                    const labelElements = [];

                    // 各バーを作成
                    data.forEach((value, index) => {
                        const barGroup = document.createElement('div');
                        barGroup.className = 'flex flex-col items-center';

                        // バー部分
                        const bar = document.createElement('div');
                        bar.className = 'w-8 md:w-12 rounded-t transition-all duration-200 cursor-pointer';
                        bar.style.height = `${calculateBarHeight(value)}px`;
                        barElements.push(bar);

                        // ホバー効果とツールチップの表示
                        bar.addEventListener('mouseenter', function() {
                            // ツールチップ
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}施設`;
                        });

                        bar.addEventListener('mouseleave', function() {
                            // ツールチップをリセット
                            tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                        });

                        // タッチデバイス対応
                        bar.addEventListener('touchstart', function() {
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}施設`;
                        });

                        // ラベル部分
                        const labelContainer = document.createElement('div');
                        labelContainer.className = 'text-xs md:text-sm mt-2 h-12 flex items-center';
                        labelElements.push(labelContainer);

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

                    const gridLines = [];
                    const gridCount = 4;
                    for (let i = 1; i <= gridCount; i++) {
                        const gridLine = document.createElement('div');
                        const position = 100 - (i / gridCount) * 100;
                        gridLine.className = 'absolute w-full h-px';
                        gridLine.style.bottom = `${position}%`;
                        // グリッド線に値を表示するラベルを追加
                        const gridLabel = document.createElement('div');
                        gridLabel.className = 'absolute -left-1 text-xs opacity-70';
                        gridLabel.style.bottom = `${position}%`;
                        gridLabel.style.transform = 'translateY(50%)';
                        // 表示最小値からの相対的な値を計算
                        const gridValue = Math.round(displayMinValue + (adjustedDataRange * i / gridCount));
                        gridLabel.textContent = gridValue.toLocaleString();

                        gridLines.push(gridLine);
                        gridContainer.appendChild(gridLine);
                        gridContainer.appendChild(gridLabel);
                    }

                    container.style.position = 'relative';
                    container.appendChild(gridContainer);

                    // ダークモードに応じてスタイルを更新する関数
                    function updateDarkModeStyles() {
                        const dark = isDarkMode();

                        // コンテナの背景色
                        container.className = `w-full ${dark ? 'bg-gray-900' : 'bg-white'}`;

                        // 最小値・最大値表示の色
                        baselineInfo.className = `text-xs text-right w-full pr-2 opacity-70 -mb-1 ${dark ? 'text-gray-400' : 'text-gray-500'}`;
                        maxValueInfo.className = `text-xs text-right w-full pr-2 opacity-70 ${dark ? 'text-gray-400' : 'text-gray-500'}`;

                        // ツールチップのテキスト色
                        tooltip.className = `mt-4 text-center text-sm font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;

                        // バーの色
                        barElements.forEach(bar => {
                            bar.className = `w-8 md:w-12 rounded-t transition-all duration-200 cursor-pointer ${dark ? 'bg-blue-500 hover:bg-blue-400' : 'bg-blue-500 hover:bg-blue-600'}`;
                        });

                        // ラベルの色
                        labelElements.forEach(label => {
                            label.className = `text-xs md:text-sm mt-2 h-12 flex items-center ${dark ? 'text-gray-300' : 'text-gray-600'}`;
                        });

                        // グリッド線の色
                        gridLines.forEach(line => {
                            line.className = `absolute w-full h-px ${dark ? 'bg-gray-700' : 'bg-gray-100'}`;
                        });
                    }

                    // 初期スタイルの設定
                    updateDarkModeStyles();

                    // メディアクエリの変更検出（システムの色モード変更を検知）
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateDarkModeStyles);
                });
            })();
        </script>

        <!-- 棒グラフの表示エリア -->
        <div class="bar-chart-container w-full bg-white dark:bg-gray-900">
            <!-- グラフ要素はJavaScriptで動的に生成されます -->
        </div>
    </div>
</x-chart.base>
