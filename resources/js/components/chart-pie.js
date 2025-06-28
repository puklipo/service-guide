export default function chartPie(options) {
    return {
        init() {
            this.$nextTick(() => {
                const container = this.$refs.container;
                const legendContainer = this.$refs.legendContainer;
                if (!container || !legendContainer) return;

                const { data, labels, total, colors } = options;
                const chartId = 'pie-chart-' + Math.random().toString(36).substring(2, 9);
                const tooltip = this.$refs.tooltip;

                const isDarkMode = () => {
                    return document.documentElement.classList.contains('dark') ||
                           window.matchMedia('(prefers-color-scheme: dark)').matches;
                };

                while (container.firstChild) container.removeChild(container.firstChild);
                while (legendContainer.firstChild) legendContainer.removeChild(legendContainer.firstChild);

                const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                svg.setAttribute("viewBox", "0 0 100 100");
                svg.setAttribute("class", "w-full h-full max-w-md mx-auto");

                const cx = 50;
                const cy = 50;
                const r = 40;
                let startAngle = -Math.PI / 2;

                const segments = [];
                const legendLabels = [];

                data.forEach((value, index) => {
                    if (value <= 0) return;

                    const angle = (value / total) * (Math.PI * 2);
                    const endAngle = startAngle + angle;
                    const x1 = cx + r * Math.cos(startAngle);
                    const y1 = cy + r * Math.sin(startAngle);
                    const x2 = cx + r * Math.cos(endAngle);
                    const y2 = cy + r * Math.sin(endAngle);
                    const largeArcFlag = angle > Math.PI ? 1 : 0;

                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", `M ${cx},${cy} L ${x1},${y1} A ${r},${r} 0 ${largeArcFlag} 1 ${x2},${y2} Z`);
                    path.setAttribute("fill", colors[index % colors.length]);
                    path.setAttribute("class", "transition-opacity duration-200 cursor-pointer");

                    segments.push({
                        path,
                        value,
                        label: labels[index],
                        color: colors[index % colors.length],
                        percent: ((value / total) * 100).toFixed(1)
                    });

                    path.addEventListener('mouseenter', () => {
                        path.setAttribute("opacity", "0.85");
                        path.setAttribute("stroke-width", "1.2");
                        tooltip.textContent = `${labels[index]}: ${value.toLocaleString()} (${((value / total) * 100).toFixed(1)}%)`;
                    });

                    path.addEventListener('mouseleave', () => {
                        path.setAttribute("opacity", "1");
                        path.setAttribute("stroke-width", "0.7");
                        tooltip.textContent = '円グラフのセグメントにカーソルを合わせると詳細が表示されます';
                    });

                    svg.appendChild(path);
                    startAngle = endAngle;
                });

                container.appendChild(svg);

                const legend = document.createElement('div');
                legend.className = 'flex flex-wrap justify-center gap-4 py-2';

                segments.forEach((segment) => {
                    const legendItem = document.createElement('div');
                    legendItem.className = 'flex items-center';

                    const colorBox = document.createElement('div');
                    colorBox.className = 'w-4 h-4 mr-2 rounded-sm';
                    colorBox.style.backgroundColor = segment.color;

                    const label = document.createElement('span');
                    label.className = 'text-sm';
                    label.textContent = segment.label;
                    legendLabels.push(label);

                    legendItem.appendChild(colorBox);
                    legendItem.appendChild(label);

                    legendItem.addEventListener('mouseenter', () => {
                        segment.path.setAttribute("opacity", "0.85");
                        segment.path.setAttribute("stroke-width", "1.2");
                        tooltip.textContent = `${segment.label}: ${segment.value.toLocaleString()} (${segment.percent}%)`;
                    });

                    legendItem.addEventListener('mouseleave', () => {
                        segment.path.setAttribute("opacity", "1");
                        segment.path.setAttribute("stroke-width", "0.7");
                        tooltip.textContent = '円グラフのセグメントにカーソルを合わせると詳細が表示されます';
                    });

                    legend.appendChild(legendItem);
                });

                legendContainer.appendChild(legend);

                function updateDarkModeStyles() {
                    const dark = isDarkMode();
                    container.className = `pie-chart-container w-64 h-64 mx-auto ${dark ? 'bg-gray-900' : 'bg-white'}`;
                    tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
                    segments.forEach(segment => {
                        segment.path.setAttribute("stroke", dark ? "#1f2937" : "white");
                        segment.path.setAttribute("stroke-width", "0.7");
                    });
                    legendLabels.forEach(label => {
                        label.className = `text-sm ${dark ? 'text-gray-300' : 'text-gray-700'}`;
                    });
                    legendContainer.className = `pie-chart-legend mt-4 w-full ${dark ? 'bg-gray-900' : 'bg-white'}`;
                }

                updateDarkModeStyles();

                const darkModeObserver = new MutationObserver(updateDarkModeStyles);
                darkModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateDarkModeStyles);
            });
        }
    };
}
