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
    @endphp

    <div class="chart-bar-component w-full" x-data="chartBar({
        data: {{ json_encode($data) }},
        labels: {{ json_encode($labels) }},
        maxValue: {{ json_encode($maxValue) }},
        minValue: {{ json_encode($minValue) }},
        displayMinValue: {{ json_encode($displayMinValue) }},
        adjustedDataRange: {{ json_encode($adjustedDataRange) }}
    })" x-init="init()">
        <div x-ref="container" class="bar-chart-container w-full bg-white dark:bg-gray-900 rounded-md">
            <!-- Alpine will generate the chart here -->
        </div>
    </div>
</x-chart.base>

@once
<script>
    function chartBar(options) {
        return {
            init() {
                this.$nextTick(() => {
                    const container = this.$refs.container;
                    if (!container) return;

                    const { data, labels, maxValue, minValue, displayMinValue, adjustedDataRange } = options;
                    const chartId = 'bar-chart-' + Math.random().toString(36).substring(2, 9);

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
                    const barSpacing = 8;

                    function calculateBarHeight(value) {
                        if (adjustedDataRange === 0) return 0;
                        return ((value - displayMinValue) / adjustedDataRange) * (height - paddingBottom - paddingTop);
                    }

                    while (container.firstChild) {
                        container.removeChild(container.firstChild);
                    }

                    const darkModeObserver = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'class') {
                                updateDarkModeStyles();
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

                    const barElements = [];
                    const labelElements = [];
                    const gridLines = [];
                    const gridLabels = [];

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

                    const shouldRotateLabels = data.length > 5;
                    const labelRotationAngle = shouldRotateLabels ? -25 : 0;
                    const labelYOffset = shouldRotateLabels ? 30 : 20;

                    const chartWidth = width - paddingLeft - paddingRight;
                    const totalBarSpace = chartWidth - (barSpacing * (data.length - 1));
                    const barWidth = totalBarSpace / data.length;

                    data.forEach((value, index) => {
                        const barHeight = calculateBarHeight(value);
                        const x = paddingLeft + (index * (barWidth + barSpacing));
                        const y = height - paddingBottom - barHeight;

                        const bar = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                        bar.setAttribute("x", x);
                        bar.setAttribute("y", y);
                        bar.setAttribute("width", barWidth);
                        bar.setAttribute("height", barHeight);
                        bar.setAttribute("rx", "2");
                        bar.classList.add("cursor-pointer", "transition-all", "duration-200");
                        barElements.push(bar);

                        bar.addEventListener('mouseenter', () => {
                            bar.setAttribute("opacity", "0.8");
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}`;
                        });

                        bar.addEventListener('mouseleave', () => {
                            bar.setAttribute("opacity", "1");
                            tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                        });

                        bar.addEventListener('touchstart', () => {
                            tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}`;
                        });

                        svg.appendChild(bar);

                        const label = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        label.setAttribute("x", x + (barWidth / 2));
                        label.setAttribute("y", height - paddingBottom + labelYOffset);

                        if (shouldRotateLabels) {
                            label.setAttribute("transform", `rotate(${labelRotationAngle} ${x + (barWidth / 2)}, ${height - paddingBottom + 10})`);
                            label.setAttribute("text-anchor", "end");
                        } else {
                            label.setAttribute("text-anchor", "middle");
                        }

                        label.setAttribute("font-size", "12");
                        label.textContent = labels[index];
                        labelElements.push(label);
                        svg.appendChild(label);
                    });

                    container.appendChild(svg);

                    const tooltip = document.createElement('div');
                    tooltip.id = `${chartId}-tooltip`;
                    tooltip.className = 'mt-4 text-center text-sm font-medium';
                    tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                    container.appendChild(tooltip);

                    function updateDarkModeStyles() {
                        const dark = isDarkMode();
                        container.className = `bar-chart-container w-full rounded-md ${dark ? 'bg-gray-900' : 'bg-white'}`;
                        rangeInfo.className = `text-xs text-right w-full pr-2 opacity-70 mb-4 ${dark ? 'text-gray-400' : 'text-gray-500'}`;
                        tooltip.className = `mt-4 text-center text-sm font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
                        gridLines.forEach(line => line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"));
                        yAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
                        xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
                        gridLabels.forEach(label => label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
                        barElements.forEach(bar => bar.setAttribute("fill", dark ? "#3b82f6" : "#3b82f6"));
                        labelElements.forEach(label => label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
                    }

                    updateDarkModeStyles();
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateDarkModeStyles);
                });
            }
        };
    }
</script>
@endonce
