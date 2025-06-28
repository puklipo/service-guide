@props(['data', 'labels', 'maxValue' => null, 'title' => null])

<x-chart.base :title="$title">
    @php
    // 最大値が指定されていなければ、データの最大値に2%余裕を持たせる
    $dataMaxValue = max($data);
    $maxValue = $maxValue ?? round($dataMaxValue * 1.02);

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

    // ユニークなID接頭辞を生成（複数のグラフがある場合に要素の衝突を防ぐ）
    $chartId = 'bar-chart-' . uniqid();
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
                const chartId = @json($chartId); // ユニークID接頭辞

                // ダークモードかどうかを検出
                const isDarkMode = () => {
                    return document.documentElement.classList.contains('dark') ||
                           window.matchMedia('(prefers-color-scheme: dark)').matches;
                };

                // グラフのパラメータ
                const maxBarHeightPx = 256; // 最大の高さ（ピクセル）
                const barSpacing = 8; // バー間のスペース（より狭く）
                const labelHeight = 48; // ラベルの高さ

                // グラフの寸法
                const width = 800;
                const height = 400;
                const paddingBottom = 60; // ラベル用に余白を増やす
                const paddingLeft = 60; // Y軸ラベル用に余白を確保
                const paddingTop = 20; // 上部の余白
                const paddingRight = 20; // 右側の余白

                // 高さを計算（調整後の最小値を考慮）
                function calculateBarHeight(value) {
                    // 値の相対位置に基づいて高さを計算（調整後の最小値からの相対的な高さ）
                    return ((value - displayMinValue) / adjustedDataRange) * (height - paddingBottom - paddingTop);
                }

                // DOMの準備ができたら実行
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.querySelector(`#${chartId}-container`);
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

                    // 表示範囲の情報を上部に追加
                    const rangeInfo = document.createElement('div');
                    rangeInfo.className = 'text-xs text-right w-full pr-2 opacity-70 mb-4';
                    rangeInfo.textContent = `表示範囲: ${displayMinValue.toLocaleString()} 〜 ${maxValue.toLocaleString()}`;
                    container.appendChild(rangeInfo);

                    // SVG要素を作成
                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
                    svg.setAttribute("class", "w-full h-64 bg-opacity-50");

                    // バーの配列を保持（ダークモード切り替え時に参照するため）
                    const barElements = [];
                    const labelElements = [];
                    const gridLines = [];
                    const gridLabels = [];

                    // グリッド線を追加
                    const gridCount = 5;
                    for (let i = 0; i < gridCount; i++) {
                        const ratio = i / (gridCount - 1);
                        const y = height - paddingBottom - (ratio * (height - paddingBottom - paddingTop));

                        // グリッド線
                        const gridLine = document.createElementNS("http://www.w3.org/2000/svg", "line");
                        gridLine.setAttribute("x1", paddingLeft);
                        gridLine.setAttribute("y1", y);
                        gridLine.setAttribute("x2", width - paddingRight);
                        gridLine.setAttribute("y2", y);
                        gridLine.setAttribute("stroke-width", "1");
                        gridLines.push(gridLine);
                        svg.appendChild(gridLine);

                        // Y軸ラベル（値）
                        const yValue = displayMinValue + (adjustedDataRange * ratio);
                        const valueLabel = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        valueLabel.setAttribute("x", paddingLeft - 10);
                        valueLabel.setAttribute("y", y + 4); // テキスト位置微調整
                        valueLabel.setAttribute("text-anchor", "end");
                        valueLabel.setAttribute("font-size", "12");
                        valueLabel.setAttribute("class", "text-xs");
                        valueLabel.textContent = Math.round(yValue).toLocaleString();
                        gridLabels.push(valueLabel);
                        svg.appendChild(valueLabel);
                    }

                    // Y軸を追加
                    const yAxis = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    yAxis.setAttribute("x1", paddingLeft);
                    yAxis.setAttribute("y1", paddingTop);
                    yAxis.setAttribute("x2", paddingLeft);
                    yAxis.setAttribute("y2", height - paddingBottom);
                    svg.appendChild(yAxis);

                    // X軸を追加
                    const xAxis = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    xAxis.setAttribute("x1", paddingLeft);
                    xAxis.setAttribute("y1", height - paddingBottom);
                    xAxis.setAttribute("x2", width - paddingRight);
                    xAxis.setAttribute("y2", height - paddingBottom);
                    svg.appendChild(xAxis);

                    // X軸ラベル表示の設定
                    // データ数に応じて表示方法を調整
                    const shouldRotateLabels = data.length > 5; // データが6つ以上ならラベルを回転
                    const labelRotationAngle = shouldRotateLabels ? -25 : 0; // 回転角度
                    const labelYOffset = shouldRotateLabels ? 30 : 20; // Y方向オフセット

                    // 各バーを計算して描画（幅一杯に表示）
                    const chartWidth = width - paddingLeft - paddingRight;
                    // 全体のスペースからデータ数に応じてバーの幅を計算
                    const totalBarSpace = chartWidth - (barSpacing * (data.length - 1));
                    const barWidth = totalBarSpace / data.length;

                    // 各バーを作成
                    data.forEach((value, index) => {
                        const barHeight = calculateBarHeight(value);
                        const x = paddingLeft + (index * (barWidth + barSpacing));
                        const y = height - paddingBottom - barHeight;

                        // バー部分
                        const bar = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                        bar.setAttribute("x", x);
                        bar.setAttribute("y", y);
                        bar.setAttribute("width", barWidth);
                        bar.setAttribute("height", barHeight);
                        bar.setAttribute("rx", "2"); // 角を丸める
                        bar.classList.add("cursor-pointer", "transition-all", "duration-200");
                        barElements.push(bar);

                        // ホバー効果
                        bar.addEventListener('mouseenter', function() {
                            bar.setAttribute("opacity", "0.8");
                            const tooltip = document.querySelector(`#${chartId}-tooltip`);
                            if (tooltip) {
                                tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}`;
                            }
                        });

                        bar.addEventListener('mouseleave', function() {
                            bar.setAttribute("opacity", "1");
                            const tooltip = document.querySelector(`#${chartId}-tooltip`);
                            if (tooltip) {
                                tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                            }
                        });

                        // タッチデバイス対応
                        bar.addEventListener('touchstart', function() {
                            const tooltip = document.querySelector(`#${chartId}-tooltip`);
                            if (tooltip) {
                                tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}`;
                            }
                        });

                        svg.appendChild(bar);

                        // ラベル部分
                        const label = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        label.setAttribute("x", x + (barWidth / 2));
                        label.setAttribute("y", height - paddingBottom + labelYOffset);

                        // ラベルの回転と位置調整
                        if (shouldRotateLabels) {
                            label.setAttribute("transform", `rotate(${labelRotationAngle} ${x + (barWidth / 2)}, ${height - paddingBottom + 10})`);
                            label.setAttribute("text-anchor", "end"); // 回転時は右寄せ
                        } else {
                            label.setAttribute("text-anchor", "middle"); // 通常時は中央寄せ
                        }

                        label.setAttribute("font-size", "12");
                        label.textContent = labels[index];
                        labelElements.push(label);
                        svg.appendChild(label);
                    });

                    // SVGをコンテナに追加
                    container.appendChild(svg);

                    // ツールチップ
                    const tooltip = document.createElement('div');
                    tooltip.id = `${chartId}-tooltip`;
                    tooltip.className = 'mt-4 text-center text-sm font-medium';
                    tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                    container.appendChild(tooltip);

                    // ダークモードに応じてスタイルを更新する関数
                    function updateDarkModeStyles() {
                        const dark = isDarkMode();

                        // コンテナの背景色
                        container.className = `w-full ${dark ? 'bg-gray-900' : 'bg-white'}`;

                        // 表示範囲の色
                        rangeInfo.className = `text-xs text-right w-full pr-2 opacity-70 mb-4 ${dark ? 'text-gray-400' : 'text-gray-500'}`;

                        // ツールチップのテキスト色
                        tooltip.className = `mt-4 text-center text-sm font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;

                        // グリッド線の色
                        gridLines.forEach(line => {
                            line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"); // dark:gray-700, light:slate-100
                        });

                        // Y軸とX軸の色
                        yAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1"); // dark:gray-600, light:slate-300
                        xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");

                        // Y軸の値ラベルの色
                        gridLabels.forEach(label => {
                            label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"); // dark:gray-400, light:slate-500
                        });

                        // バーの色
                        barElements.forEach(bar => {
                            bar.setAttribute("fill", dark ? "#3b82f6" : "#3b82f6"); // blue-500
                        });

                        // ラベルの色
                        labelElements.forEach(label => {
                            label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"); // dark:gray-400, light:slate-500
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
        <div id="{{ $chartId }}-container" class="bar-chart-container w-full bg-white dark:bg-gray-900">
            <!-- グラフ要素はJavaScriptで動的に生成されます -->
        </div>
    </div>
</x-chart.base>
