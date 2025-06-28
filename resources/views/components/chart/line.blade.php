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
                    // SVG要素を作成
                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
                    svg.setAttribute("class", "w-full h-64");

                    // ポイントを計算
                    const points = calculatePoints();

                    // 折れ線パスを追加
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", getPath(points));
                    path.setAttribute("fill", "none");
                    path.setAttribute("stroke", "#3b82f6");
                    path.setAttribute("stroke-width", "3");
                    svg.appendChild(path);

                    // X軸を追加
                    const xAxis = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    xAxis.setAttribute("x1", paddingLeft);
                    xAxis.setAttribute("y1", height - paddingBottom / 2);
                    xAxis.setAttribute("x2", width);
                    xAxis.setAttribute("y2", height - paddingBottom / 2);
                    xAxis.setAttribute("stroke", "#e5e7eb");
                    xAxis.setAttribute("stroke-width", "1");
                    svg.appendChild(xAxis);

                    // 各データポイントを追加
                    points.forEach(point => {
                        // ポイントのサークル
                        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        circle.setAttribute("cx", point.x);
                        circle.setAttribute("cy", point.y);
                        circle.setAttribute("r", "6");
                        circle.setAttribute("fill", "#3b82f6");
                        circle.setAttribute("stroke", "#ffffff");
                        circle.setAttribute("stroke-width", "2");
                        circle.classList.add("cursor-pointer");

                        // ツールチップの表示
                        circle.addEventListener('mouseover', function() {
                            const tooltip = document.getElementById('line-chart-tooltip');
                            if (tooltip) {
                                tooltip.textContent = `${point.label}: ${parseInt(point.value).toLocaleString()}`;
                            }
                        });

                        // ツールチップをリセット
                        circle.addEventListener('mouseout', function() {
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
                        text.setAttribute("font-size", "18");
                        text.setAttribute("fill", "#6b7280");
                        text.textContent = point.label;
                        svg.appendChild(text);
                    });

                    // SVGをコンテナに追加
                    const container = document.querySelector('.line-chart-container');
                    if (container) {
                        container.appendChild(svg);
                    }
                });
            })();
        </script>

        <!-- 折れ線グラフの表示エリア -->
        <div class="line-chart-container w-full">
            <!-- SVG要素はJavaScriptで動的に生成されます -->
        </div>

        <!-- ツールチップ -->
        <div id="line-chart-tooltip" class="text-center text-sm text-gray-700 mt-2">
            グラフのポイントにカーソルを合わせると詳細が表示されます
        </div>
    </div>
</x-chart.base>
