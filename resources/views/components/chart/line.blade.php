@props(['data', 'labels', 'maxValue' => null, 'title' => null])

<x-chart.base :title="$title">
    @php
        // 最大値が指定されていなければ、データの最大値を使用
        $maxValue = $maxValue ?? max($data);

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

                // ダークモードかどうかを検出
                const isDarkMode = () => {
                    return document.documentElement.classList.contains('dark') ||
                           window.matchMedia('(prefers-color-scheme: dark)').matches;
                };

                // グラフの寸法
                const width = 800;
                const height = 400;
                const paddingBottom = 40;
                const paddingLeft = 40;

                // ポイントの位置を計算
                function calculatePoints() {
                    const points = [];
                    const xStep = (width - paddingLeft) / (data.length - 1);
                    const yScale = (height - paddingBottom) / maxValue;

                    for (let i = 0; i < data.length; i++) {
                        const x = paddingLeft + i * xStep;
                        const y = height - data[i] * yScale;
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

                    // SVG要素を作成
                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
                    svg.setAttribute("class", "w-full h-64 bg-opacity-50");

                    // ポイントを計算
                    const points = calculatePoints();

                    // 参照用に要素を保持する配列
                    const gridLines = [];
                    const circles = [];
                    const axisLabels = [];

                    // グリッド線を追加
                    const gridCount = 5;
                    for (let i = 1; i < gridCount; i++) {
                        const y = height - (i * (height - paddingBottom) / gridCount);
                        const gridLine = document.createElementNS("http://www.w3.org/2000/svg", "line");
                        gridLine.setAttribute("x1", paddingLeft);
                        gridLine.setAttribute("y1", y);
                        gridLine.setAttribute("x2", width);
                        gridLine.setAttribute("y2", y);
                        gridLine.setAttribute("stroke-width", "1");
                        gridLines.push(gridLine);
                        svg.appendChild(gridLine);
                    }

                    // 折れ線パスを追加
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", getPath(points));
                    path.setAttribute("fill", "none");
                    path.setAttribute("stroke-width", "3");
                    path.setAttribute("class", "transition-all duration-300");
                    svg.appendChild(path);

                    // X軸を追加
                    const xAxis = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    xAxis.setAttribute("x1", paddingLeft);
                    xAxis.setAttribute("y1", height - paddingBottom / 2);
                    xAxis.setAttribute("x2", width);
                    xAxis.setAttribute("y2", height - paddingBottom / 2);
                    svg.appendChild(xAxis);

                    // 各データポイントを追加
                    points.forEach(point => {
                        // ポイントのサークル
                        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        circle.setAttribute("cx", point.x);
                        circle.setAttribute("cy", point.y);
                        circle.setAttribute("r", "5");
                        circle.setAttribute("stroke-width", "2");
                        circle.classList.add("cursor-pointer", "transition-all", "duration-200");
                        circles.push(circle);

                        // ホバー時のエフェクト
                        circle.addEventListener('mouseenter', function() {
                            circle.setAttribute("r", "7");
                            const tooltip = document.getElementById('line-chart-tooltip');
                            if (tooltip) {
                                tooltip.textContent = `${point.label}: ${point.value.toLocaleString()}`;
                            }
                        });

                        // ホバー解除時
                        circle.addEventListener('mouseleave', function() {
                            circle.setAttribute("r", "5");
                            const tooltip = document.getElementById('line-chart-tooltip');
                            if (tooltip) {
                                tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                            }
                        });

                        svg.appendChild(circle);

                        // X軸ラベル
                        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        text.setAttribute("x", point.x);
                        text.setAttribute("y", height - 10);
                        text.setAttribute("text-anchor", "middle");
                        text.setAttribute("font-size", "12");
                        text.setAttribute("class", "text-sm md:text-md");
                        text.textContent = point.label;
                        axisLabels.push(text);
                        svg.appendChild(text);
                    });

                    // SVGをコンテナに追加
                    container.appendChild(svg);

                    // ダークモード切替関数
                    function updateChartColors() {
                        const dark = isDarkMode();

                        // コンテナの背景色
                        container.className = `w-full rounded-md ${dark ? 'bg-gray-900' : 'bg-white'}`;

                        // ツールチップのテキスト色
                        const tooltip = document.getElementById('line-chart-tooltip');
                        if (tooltip) {
                            tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
                        }

                        // グリッド線の色
                        gridLines.forEach(line => {
                            line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"); // dark:gray-700, light:slate-100
                        });

                        // X軸の色
                        xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1"); // dark:gray-600, light:slate-300

                        // 折れ線の色
                        path.setAttribute("stroke", dark ? "#3b82f6" : "#3b82f6"); // blue-500 (同じ色でもOK)

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

        <!-- ツールチップ -->
        <div id="line-chart-tooltip" class="text-center text-sm text-gray-600 dark:text-gray-300 mt-3 font-medium">
            グラフのポイントにカーソルを合わせると詳細が表示されます
        </div>
    </div>
</x-chart.base>
