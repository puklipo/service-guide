@props(['data', 'labels', 'title' => null])

<x-chart.base :title="$title">
    @php
    // 合計値を計算
    $total = array_sum($data);

    // データとラベルをJSON形式にエンコード
    $jsonData = json_encode($data);
    $jsonLabels = json_encode($labels);

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
    $jsonColors = json_encode($colors);
    @endphp

    <div class="chart-pie-component w-full">
        <script>
            // この即時実行関数は円グラフを描画します
            (function() {
                // データとラベル
                const data = @json($data);
                const labels = @json($labels);
                const total = @json($total);
                const colors = @json($colors);

                // ダークモードかどうかを検出
                const isDarkMode = () => {
                    return document.documentElement.classList.contains('dark') ||
                           window.matchMedia('(prefers-color-scheme: dark)').matches;
                };

                // SVGコンテナ要素
                const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                svg.setAttribute("viewBox", "0 0 100 100");
                svg.setAttribute("class", "w-full h-full max-w-md mx-auto");

                // 円グラフのパラメータ
                const cx = 50;
                const cy = 50;
                const r = 40;
                let startAngle = -Math.PI / 2; // 12時の位置から開始

                // 各セグメントとそのデータを格納する配列
                const segments = [];
                // 凡例ラベル要素を格納する配列
                const legendLabels = [];
                // 凡例アイテム全体の配列
                const legendItems = [];

                // 各データポイントに対して円弧を描画
                data.forEach((value, index) => {
                    if (value <= 0) return; // 0以下の値はスキップ

                    // この項目の角度 = (値 / 合計) * 2π
                    const angle = (value / total) * (Math.PI * 2);
                    const endAngle = startAngle + angle;

                    // 円弧の始点と終点の座標を計算
                    const x1 = cx + r * Math.cos(startAngle);
                    const y1 = cy + r * Math.sin(startAngle);
                    const x2 = cx + r * Math.cos(endAngle);
                    const y2 = cy + r * Math.sin(endAngle);

                    // 大きい円弧かどうか（180度以上か）
                    const largeArcFlag = angle > Math.PI ? 1 : 0;

                    // SVGパスデータ
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", `M ${cx},${cy} L ${x1},${y1} A ${r},${r} 0 ${largeArcFlag} 1 ${x2},${y2} Z`);
                    path.setAttribute("fill", colors[index % colors.length]);
                    path.setAttribute("class", "transition-opacity duration-200 cursor-pointer");

                    // セグメントデータを格納
                    segments.push({
                        path,
                        value,
                        label: labels[index],
                        color: colors[index % colors.length],
                        percent: ((value / total) * 100).toFixed(1)
                    });

                    // マウスオーバー時の処理
                    path.addEventListener('mouseenter', () => {
                        path.setAttribute("opacity", "0.85");
                        path.setAttribute("stroke-width", "1.2");

                        const tooltip = document.getElementById('pie-tooltip');
                        if (tooltip) {
                            const percent = ((value / total) * 100).toFixed(1);
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()} (${percent}%)`;
                        }
                    });

                    // マウスアウト時の処理
                    path.addEventListener('mouseleave', () => {
                        path.setAttribute("opacity", "1");
                        path.setAttribute("stroke-width", "0.7");

                        const tooltip = document.getElementById('pie-tooltip');
                        if (tooltip) {
                            tooltip.textContent = '円グラフのセグメントにカーソルを合わせると詳細が表示されます';
                        }
                    });

                    svg.appendChild(path);

                    // 次のセグメントの開始角度を設定
                    startAngle = endAngle;
                });

                // DOMに追加
                document.addEventListener('DOMContentLoaded', () => {
                    const container = document.querySelector('.pie-chart-container');
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

                    container.appendChild(svg);

                    // 凡例を作成
                    const legend = document.createElement('div');
                    legend.className = 'flex flex-wrap justify-center gap-4 py-2';
                    legend.id = 'pie-chart-legend';

                    segments.forEach((segment, index) => {
                        const legendItem = document.createElement('div');
                        legendItem.className = 'flex items-center';
                        legendItems.push(legendItem);

                        const colorBox = document.createElement('div');
                        colorBox.className = 'w-4 h-4 mr-2 rounded-sm';
                        colorBox.style.backgroundColor = segment.color;

                        const label = document.createElement('span');
                        label.className = 'text-sm';
                        label.textContent = segment.label;
                        legendLabels.push(label);

                        legendItem.appendChild(colorBox);
                        legendItem.appendChild(label);

                        // 凡例項目のホバー効果
                        legendItem.addEventListener('mouseenter', () => {
                            segment.path.setAttribute("opacity", "0.85");
                            segment.path.setAttribute("stroke-width", "1.2");

                            const tooltip = document.getElementById('pie-tooltip');
                            if (tooltip) {
                                tooltip.textContent = `${segment.label}: ${segment.value.toLocaleString()} (${segment.percent}%)`;
                            }
                        });

                        legendItem.addEventListener('mouseleave', () => {
                            segment.path.setAttribute("opacity", "1");
                            segment.path.setAttribute("stroke-width", "0.7");

                            const tooltip = document.getElementById('pie-tooltip');
                            if (tooltip) {
                                tooltip.textContent = '円グラフのセグメントにカーソルを合わせると詳細が表示されます';
                            }
                        });

                        legend.appendChild(legendItem);
                    });

                    const legendContainer = document.querySelector('.pie-chart-legend');
                    if (legendContainer) {
                        legendContainer.appendChild(legend);

                        // 凡例コンテナにIDを追加して参照しやすくする
                        legendContainer.id = 'pie-legend-container';
                    }

                    // ダークモードに応じてスタイルを更新する関数
                    function updateDarkModeStyles() {
                        const dark = isDarkMode();

                        // コンテナの背景色
                        container.className = `w-64 h-64 mx-auto ${dark ? 'bg-gray-900' : 'bg-white'}`;

                        // ツールチップのテキスト色
                        const tooltip = document.getElementById('pie-tooltip');
                        if (tooltip) {
                            tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
                        }

                        // セグメントの区切り線の色
                        segments.forEach(segment => {
                            segment.path.setAttribute("stroke", dark ? "#1f2937" : "white"); // dark:gray-800, light:white
                            segment.path.setAttribute("stroke-width", "0.7");
                        });

                        // 凡例のテキスト色
                        legendLabels.forEach(label => {
                            label.className = `text-sm ${dark ? 'text-gray-300' : 'text-gray-700'}`;
                        });

                        // 凡例コンテナの背景色
                        const legendContainer = document.getElementById('pie-legend-container');
                        if (legendContainer) {
                            legendContainer.className = `pie-chart-legend mt-4 w-full ${dark ? 'bg-gray-900' : 'bg-white'}`;
                        }
                    }

                    // 初期スタイルの設定
                    updateDarkModeStyles();

                    // メディアクエリの変更検出（システムの色モード変更を検知）
                    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                    mediaQuery.addEventListener('change', updateDarkModeStyles);
                });
            })();
        </script>

        <!-- 円グラフの表示エリア -->
        <div class="flex flex-col items-center">
            <div class="pie-chart-container w-64 h-64 mx-auto bg-white dark:bg-gray-900">
                <!-- SVG要素はJavaScriptで動的に生成されます -->
            </div>

            <!-- ツールチップ -->
            <div id="pie-tooltip" class="text-center text-sm text-gray-600 dark:text-gray-300 mt-3 font-medium">
                円グラフのセグメントにカーソルを合わせると詳細が表示されます
            </div>

            <!-- 凡例表示エリア -->
            <div class="pie-chart-legend mt-4 w-full bg-white dark:bg-gray-900">
                <!-- 凡例要素はJavaScriptで動的に生成されます -->
            </div>
        </div>
    </div>
</x-chart.base>
