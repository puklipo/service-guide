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

        // データとラベルをJSON形式にエンコード
        $jsonData = json_encode($data);
        $jsonLabels = json_encode($labels);
    @endphp

    <div class="chart-line-component w-full">
        <script>
            // この即時実行関数は折れ線グラフを描画します
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

                // グラフの寸法
                const width = 800;
                const height = 400;
                const paddingBottom = 60; // ラベル用に余白を増やす
                const paddingLeft = 60; // Y軸ラベル用に少し広げる
                const paddingTop = 20; // 上部の余白
                const paddingRight = 20; // 右側の余白

                // X軸ラベル表示の設定
                // データ数に応じて表示方法を調整
                const shouldRotateLabels = data.length > 5; // データが6つ以上ならラベルを回転
                const labelRotationAngle = shouldRotateLabels ? -25 : 0; // 回転角度
                const labelYOffset = shouldRotateLabels ? 30 : 15; // Y方向オフセット

                // ポイントの位置を計算（調整済み最小値を基準とした相対的な位置）
                function calculatePoints() {
                    const points = [];
                    const xStep = (width - paddingLeft - paddingRight) / (data.length - 1);
                    const yScale = (height - paddingBottom - paddingTop) / adjustedDataRange; // 調整済み範囲に基づくスケール

                    for (let i = 0; i < data.length; i++) {
                        const x = paddingLeft + i * xStep;
                        // 調整済み最小値を引いて相対的な位置を計算
                        const y = height - paddingBottom - ((data[i] - displayMinValue) * yScale);
                        points.push({ x, y, value: data[i], label: labels[i] });
                    }

                    return points;
                }

                // SVGパスを生成
                function getPath(points) {
                    let path = `M ${points[0].x} ${points[0].y}`;

                    for (let i = 1; i < points.length; i++) {
                        path += ` L ${points[i].x} ${points[i].y}`;
                    }

                    return path;
                }

                // DOMの準備ができたら実行
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.querySelector('.line-chart-container');
                    if (!container) return;

                    // ダークモードの監視
                    const darkModeObserver = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'class') {
                                updateChartColors();
                            }
                        });
                    });

                    darkModeObserver.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });

                    // 表示範囲の情報を追加
                    const rangeInfo = document.createElement('div');
                    rangeInfo.className = 'text-xs text-right w-full pr-2 opacity-70 mb-4';
                    rangeInfo.textContent = `表示範囲: ${displayMinValue.toLocaleString()} 〜 ${maxValue.toLocaleString()}`;
                    container.appendChild(rangeInfo);

                    // SVG要素を作成
                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
                    svg.setAttribute("class", "w-full h-64 bg-opacity-50");

                    // ポイントを計算
                    const points = calculatePoints();

                    // 参照用に要素を保持する配列
                    const gridLines = [];
                    const gridLabels = [];
                    const circles = [];
                    const axisLabels = [];

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

                    // 折れ線パスを追加
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", getPath(points));
                    path.setAttribute("fill", "none");
                    path.setAttribute("stroke-width", "4");  // 線を太く（3から4に変更）
                    path.setAttribute("class", "transition-all duration-300");
                    svg.appendChild(path);

                    // 各データポイントを追加
                    points.forEach(point => {
                        // ポイントのサークル
                        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        circle.setAttribute("cx", point.x);
                        circle.setAttribute("cy", point.y);
                        circle.setAttribute("r", "6");  // ポイントを大きく（5から6に変更）
                        circle.setAttribute("stroke-width", "2");
                        circle.classList.add("cursor-pointer", "transition-all", "duration-200");
                        circles.push(circle);

                        // ホバー時のエフェクト
                        circle.addEventListener('mouseenter', function() {
                            circle.setAttribute("r", "8");  // ホバー時のサイズも大きく（7から8に変更）
                            const tooltip = document.getElementById('line-chart-tooltip');
                            if (tooltip) {
                                tooltip.textContent = `${point.label}: ${point.value.toLocaleString()}`;
                            }
                        });

                        // ホバー解除時
                        circle.addEventListener('mouseleave', function() {
                            circle.setAttribute("r", "6");  // 元のサイズも変更（5から6に変更）
                            const tooltip = document.getElementById('line-chart-tooltip');
                            if (tooltip) {
                                tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                            }
                        });

                        svg.appendChild(circle);

                        // X軸ラベル
                        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        text.setAttribute("x", point.x);
                        text.setAttribute("y", height - paddingBottom + labelYOffset);

                        // ラベルの回転と位置調整
                        if (shouldRotateLabels) {
                            text.setAttribute("transform", `rotate(${labelRotationAngle} ${point.x}, ${height - paddingBottom + 10})`);
                            text.setAttribute("text-anchor", "end"); // 回転時は右寄せ
                        } else {
                            text.setAttribute("text-anchor", "middle"); // 通常時は中央寄せ
                        }

                        text.setAttribute("font-size", "12");
                        text.setAttribute("class", "text-sm");
                        text.textContent = point.label;
                        axisLabels.push(text);
                        svg.appendChild(text);
                    });

                    // SVGをコンテナに追加
                    container.appendChild(svg);

                    // ツールチップ
                    const tooltip = document.createElement('div');
                    tooltip.id = 'line-chart-tooltip';
                    tooltip.className = 'mt-4 text-center text-sm font-medium';
                    tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                    container.appendChild(tooltip);

                    // ダークモード切替関数
                    function updateChartColors() {
                        const dark = isDarkMode();

                        // コンテナの背景色
                        container.className = `w-full rounded-md ${dark ? 'bg-gray-900' : 'bg-white'}`;

                        // 範囲情報の色
                        rangeInfo.className = `text-xs text-right w-full pr-2 opacity-70 mb-4 ${dark ? 'text-gray-400' : 'text-gray-500'}`;

                        // ツールチップのテキスト色
                        tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;

                        // グリッド線の色
                        gridLines.forEach(line => {
                            line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"); // dark:gray-700, light:slate-100
                        });

                        // Y軸の値ラベルの色
                        gridLabels.forEach(label => {
                            label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"); // dark:gray-400, light:slate-500
                        });

                        // X軸とY軸の色
                        xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1"); // dark:gray-600, light:slate-300
                        yAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");

                        // 折れ線の色
                        path.setAttribute("stroke", dark ? "#3b82f6" : "#3b82f6"); // blue-500

                        // データポイントの色
                        circles.forEach(circle => {
                            circle.setAttribute("fill", dark ? "#3b82f6" : "#3b82f6"); // blue-500
                            circle.setAttribute("stroke", dark ? "#1e293b" : "#ffffff"); // dark:slate-800, light:white
                        });

                        // X軸ラベルの色
                        axisLabels.forEach(text => {
                            text.setAttribute("fill", dark ? "#9ca3af" : "#64748b"); // dark:gray-400, light:slate-500
                        });
                    }

                    // 初期スタイルの設定
                    updateChartColors();

                    // メディアクエリの変更検出（システムの色モード変更を検知）
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateChartColors);
                });
            })();
        </script>

        <!-- 折れ線グラフの表示エリア -->
        <div class="line-chart-container w-full bg-white dark:bg-gray-900 rounded-md">
            <!-- SVG要素はJavaScriptで動的に生成されます -->
        </div>
    </div>
</x-chart.base>
