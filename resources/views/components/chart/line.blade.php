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
    @endphp

    <div class="chart-line-component w-full" x-data="chartLine({
        data: {{ json_encode($data) }},
        labels: {{ json_encode($labels) }},
        maxValue: {{ json_encode($maxValue) }},
        displayMinValue: {{ json_encode($displayMinValue) }},
        adjustedDataRange: {{ json_encode($adjustedDataRange) }}
    })" x-init="init()">
        <div x-ref="container" class="line-chart-container w-full bg-white dark:bg-gray-900 rounded-md">
            <!-- Alpine will generate the chart here -->
        </div>
    </div>
</x-chart.base>

@once
<script>
    function chartLine(options) {
        return {
            init() {
                this.$nextTick(() => {
                    const container = this.$refs.container;
                    if (!container) return;

                    const { data, labels, maxValue, displayMinValue, adjustedDataRange } = options;
                    const chartId = 'line-chart-' + Math.random().toString(36).substring(2, 9);

                    const isDarkMode = () => {
                        return document.documentElement.classList.contains('dark') ||
                               window.matchMedia('(prefers-color-scheme: dark)').matches;
                    };

                    const width = 800;
                    const height = 400;
                    const paddingBottom = 60;
                    const paddingLeft = 60;
                    const paddingTop = 20;
                    const paddingRight = 20;

                    const shouldRotateLabels = data.length > 5;
                    const labelRotationAngle = shouldRotateLabels ? -25 : 0;
                    const labelYOffset = shouldRotateLabels ? 30 : 15;

                    function calculatePoints() {
                        const points = [];
                        if (data.length <= 1) {
                            const x = paddingLeft + (width - paddingLeft - paddingRight) / 2;
                            const y = height - paddingBottom - ((data[0] - displayMinValue) * ((height - paddingBottom - paddingTop) / adjustedDataRange));
                            points.push({ x, y, value: data[0], label: labels[0] });
                            return points;
                        }

                        const xStep = (width - paddingLeft - paddingRight) / (data.length - 1);
                        const yScale = adjustedDataRange === 0 ? 0 : (height - paddingBottom - paddingTop) / adjustedDataRange;

                        for (let i = 0; i < data.length; i++) {
                            const x = paddingLeft + i * xStep;
                            const y = height - paddingBottom - ((data[i] - displayMinValue) * yScale);
                            points.push({ x, y, value: data[i], label: labels[i] });
                        }
                        return points;
                    }

                    function getPath(points) {
                        if (points.length < 2) return '';
                        let path = `M ${points[0].x} ${points[0].y}`;
                        for (let i = 1; i < points.length; i++) {
                            path += ` L ${points[i].x} ${points[i].y}`;
                        }
                        return path;
                    }

                    while (container.firstChild) {
                        container.removeChild(container.firstChild);
                    }

                    const darkModeObserver = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'class') {
                                updateChartColors();
                            }
                        });
                    });

                    darkModeObserver.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });

                    const rangeInfo = document.createElement('div');
                    rangeInfo.className = 'text-xs text-right w-full pr-2 opacity-70 mb-4';
                    rangeInfo.textContent = `表示範囲: ${displayMinValue.toLocaleString()} 〜 ${maxValue.toLocaleString()}`;
                    container.appendChild(rangeInfo);

                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
                    svg.setAttribute("class", "w-full h-64 bg-opacity-50");

                    const points = calculatePoints();
                    const gridLines = [];
                    const gridLabels = [];
                    const circles = [];
                    const axisLabels = [];

                    const gridCount = 5;
                    for (let i = 0; i < gridCount; i++) {
                        const ratio = i / (gridCount - 1);
                        const y = height - paddingBottom - (ratio * (height - paddingBottom - paddingTop));
                        const gridLine = document.createElementNS("http://www.w3.org/2000/svg", "line");
                        gridLine.setAttribute("x1", paddingLeft);
                        gridLine.setAttribute("y1", y);
                        gridLine.setAttribute("x2", width - paddingRight);
                        gridLine.setAttribute("y2", y);
                        gridLine.setAttribute("stroke-width", "1");
                        gridLines.push(gridLine);
                        svg.appendChild(gridLine);

                        const yValue = displayMinValue + (adjustedDataRange * ratio);
                        const valueLabel = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        valueLabel.setAttribute("x", paddingLeft - 10);
                        valueLabel.setAttribute("y", y + 4);
                        valueLabel.setAttribute("text-anchor", "end");
                        valueLabel.setAttribute("font-size", "12");
                        valueLabel.setAttribute("class", "text-xs");
                        valueLabel.textContent = Math.round(yValue).toLocaleString();
                        gridLabels.push(valueLabel);
                        svg.appendChild(valueLabel);
                    }

                    const yAxis = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    yAxis.setAttribute("x1", paddingLeft);
                    yAxis.setAttribute("y1", paddingTop);
                    yAxis.setAttribute("x2", paddingLeft);
                    yAxis.setAttribute("y2", height - paddingBottom);
                    svg.appendChild(yAxis);

                    const xAxis = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    xAxis.setAttribute("x1", paddingLeft);
                    xAxis.setAttribute("y1", height - paddingBottom);
                    xAxis.setAttribute("x2", width - paddingRight);
                    xAxis.setAttribute("y2", height - paddingBottom);
                    svg.appendChild(xAxis);

                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", getPath(points));
                    path.setAttribute("fill", "none");
                    path.setAttribute("stroke-width", "4");
                    path.setAttribute("class", "transition-all duration-300");
                    svg.appendChild(path);

                    points.forEach(point => {
                        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        circle.setAttribute("cx", point.x);
                        circle.setAttribute("cy", point.y);
                        circle.setAttribute("r", "6");
                        circle.setAttribute("stroke-width", "2");
                        circle.classList.add("cursor-pointer", "transition-all", "duration-200");
                        circles.push(circle);

                        circle.addEventListener('mouseenter', () => {
                            circle.setAttribute("r", "8");
                            tooltip.textContent = `${point.label}: ${point.value.toLocaleString()}`;
                        });

                        circle.addEventListener('mouseleave', () => {
                            circle.setAttribute("r", "6");
                            tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                        });

                        svg.appendChild(circle);

                        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        text.setAttribute("x", point.x);
                        text.setAttribute("y", height - paddingBottom + labelYOffset);

                        if (shouldRotateLabels) {
                            text.setAttribute("transform", `rotate(${labelRotationAngle} ${point.x}, ${height - paddingBottom + 10})`);
                            text.setAttribute("text-anchor", "end");
                        } else {
                            text.setAttribute("text-anchor", "middle");
                        }

                        text.setAttribute("font-size", "12");
                        text.setAttribute("class", "text-sm");
                        text.textContent = point.label;
                        axisLabels.push(text);
                        svg.appendChild(text);
                    });

                    container.appendChild(svg);

                    const tooltip = document.createElement('div');
                    tooltip.id = `${chartId}-tooltip`;
                    tooltip.className = 'mt-4 text-center text-sm font-medium';
                    tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                    container.appendChild(tooltip);

                    function updateChartColors() {
                        const dark = isDarkMode();
                        container.className = `line-chart-container w-full rounded-md ${dark ? 'bg-gray-900' : 'bg-white'}`;
                        rangeInfo.className = `text-xs text-right w-full pr-2 opacity-70 mb-4 ${dark ? 'text-gray-400' : 'text-gray-500'}`;
                        tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
                        gridLines.forEach(line => line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"));
                        gridLabels.forEach(label => label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
                        xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
                        yAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
                        path.setAttribute("stroke", dark ? "#3b82f6" : "#3b82f6");
                        circles.forEach(circle => {
                            circle.setAttribute("fill", dark ? "#3b82f6" : "#3b82f6");
                            circle.setAttribute("stroke", dark ? "#1e293b" : "#ffffff");
                        });
                        axisLabels.forEach(text => text.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
                    }

                    updateChartColors();
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateChartColors);
                });
            }
        };
    }
</script>
@endonce