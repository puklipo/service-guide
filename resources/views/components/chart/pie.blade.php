@props(['data', 'labels', 'title' => null])

<x-chart.base :title="$title">
    @php
    // 合計値を計算
    $total = array_sum($data);

    // データとラベルをJSON形式にエンコード
    $jsonData = json_encode($data);
    $jsonLabels = json_encode($labels);

    // 固定の色リスト
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
                // SVGコンテナ要素
                const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                svg.setAttribute("viewBox", "0 0 100 100");
                svg.setAttribute("class", "w-full h-full");

                // データとラベル
                const data = @json($data);
                const labels = @json($labels);
                const total = @json($total);
                const colors = @json($colors);

                // 円グラフのパラメータ
                const cx = 50;
                const cy = 50;
                const r = 40;
                let startAngle = -Math.PI / 2; // 12時の位置から開始

                // 各データポイントに対して円弧を描画
                data.forEach((value, index) => {
                    if (value <= 0) return; // 0以下の値はスキップ

                    // この���目の角度 = (値 / 合計) * 2π
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
                    path.setAttribute("stroke", "white");
                    path.setAttribute("stroke-width", "0.5");

                    // マウスオーバー時の処理
                    path.addEventListener('mouseover', () => {
                        path.setAttribute("fill-opacity", "0.8");
                        const tooltip = document.getElementById('pie-tooltip');
                        if (tooltip) {
                            const percent = ((value / total) * 100).toFixed(1);
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()} (${percent}%)`;
                        }
                    });

                    // マウスアウト時の処理
                    path.addEventListener('mouseout', () => {
                        path.setAttribute("fill-opacity", "1");
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
                    if (container) {
                        container.appendChild(svg);
                    }
                });
            })();
        </script>

        <!-- 円グラフの表示エリア -->
        <div class="flex justify-center">
            <div class="relative w-64 h-64 pie-chart-container">
                <!-- SVG要素はJavaScriptで動的に生成されます -->
            </div>
        </div>

        <!-- 凡例 -->
        <div class="flex flex-wrap justify-center mt-4 gap-2">
            @foreach($labels as $index => $label)
                <div class="flex items-center text-sm">
                    <div style="background-color: {{ $colors[$index % count($colors)] }};" class="w-4 h-4 mr-1"></div>
                    <span>{{ $label }}</span>
                </div>
            @endforeach
        </div>

        <!-- ツールチップ -->
        <div id="pie-tooltip" class="text-sm mt-4 text-center font-medium">
            円グラフのセグメントにカーソルを合わせると詳細が表示されます
        </div>
    </div>
</x-chart.base>
