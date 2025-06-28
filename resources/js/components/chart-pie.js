const isDarkMode = () => {
    return document.documentElement.classList.contains('dark') ||
           window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const createSvgElement = (tag, attributes) => {
    const el = document.createElementNS("http://www.w3.org/2000/svg", tag);
    for (const key in attributes) {
        el.setAttribute(key, attributes[key]);
    }
    return el;
};

export default function chartPie(options) {
    return {
        // State
        container: null,
        legendContainer: null,
        tooltip: null,
        svg: null,
        segments: [],
        legendLabels: [],
        darkModeObserver: null,

        // Options
        ...options,

        init() {
            this.container = this.$refs.container;
            this.legendContainer = this.$refs.legendContainer;
            this.tooltip = this.$refs.tooltip;
            if (!this.container || !this.legendContainer || !this.tooltip) return;

            this.$nextTick(() => {
                this.setupBaseElements();
                this.renderPieAndLegend();
                this.setupEventListeners();
                this.updateDarkModeStyles();
            });
        },

        setupBaseElements() {
            while (this.container.firstChild) this.container.removeChild(this.container.firstChild);
            while (this.legendContainer.firstChild) this.legendContainer.removeChild(this.legendContainer.firstChild);

            this.svg = createSvgElement("svg", {
                viewBox: "0 0 100 100",
                class: "w-full h-full max-w-md mx-auto"
            });
            this.container.appendChild(this.svg);
        },

        renderPieAndLegend() {
            const cx = 50;
            const cy = 50;
            const r = 40;
            let startAngle = -Math.PI / 2;

            const legend = document.createElement('div');
            legend.className = 'flex flex-wrap justify-center gap-4 py-2';

            this.data.forEach((value, index) => {
                if (value <= 0) return;

                const angle = (value / this.total) * (Math.PI * 2);
                const endAngle = startAngle + angle;
                const x1 = cx + r * Math.cos(startAngle);
                const y1 = cy + r * Math.sin(startAngle);
                const x2 = cx + r * Math.cos(endAngle);
                const y2 = cy + r * Math.sin(endAngle);
                const largeArcFlag = angle > Math.PI ? 1 : 0;

                const path = createSvgElement("path", {
                    d: `M ${cx},${cy} L ${x1},${y1} A ${r},${r} 0 ${largeArcFlag} 1 ${x2},${y2} Z`,
                    fill: this.colors[index % this.colors.length],
                    class: "transition-opacity duration-200 cursor-pointer"
                });
                this.svg.appendChild(path);

                const segment = {
                    path,
                    value,
                    label: this.labels[index],
                    color: this.colors[index % this.colors.length],
                    percent: ((value / this.total) * 100).toFixed(1)
                };
                this.segments.push(segment);

                path.addEventListener('mouseenter', () => this.showTooltip(segment));
                path.addEventListener('mouseleave', () => this.hideTooltip(segment));

                startAngle = endAngle;

                // Legend Item
                const legendItem = document.createElement('div');
                legendItem.className = 'flex items-center';
                legendItem.addEventListener('mouseenter', () => this.showTooltip(segment));
                legendItem.addEventListener('mouseleave', () => this.hideTooltip(segment));

                const colorBox = document.createElement('div');
                colorBox.className = 'w-4 h-4 mr-2 rounded-sm';
                colorBox.style.backgroundColor = segment.color;

                const label = document.createElement('span');
                label.className = 'text-sm';
                label.textContent = segment.label;
                this.legendLabels.push(label);

                legendItem.appendChild(colorBox);
                legendItem.appendChild(label);
                legend.appendChild(legendItem);
            });

            this.legendContainer.appendChild(legend);
        },

        showTooltip(segment) {
            segment.path.setAttribute("opacity", "0.85");
            segment.path.setAttribute("stroke-width", "1.2");
            this.tooltip.textContent = `${segment.label}: ${segment.value.toLocaleString()} (${segment.percent}%)`;
        },

        hideTooltip(segment) {
            segment.path.setAttribute("opacity", "1");
            segment.path.setAttribute("stroke-width", "0.7");
            this.tooltip.textContent = '円グラフのセグメントにカーソルを合わせると詳細が表示されます';
        },

        setupEventListeners() {
            this.darkModeObserver = new MutationObserver(() => this.updateDarkModeStyles());
            this.darkModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => this.updateDarkModeStyles());
        },

        updateDarkModeStyles() {
            const dark = isDarkMode();
            this.container.className = `pie-chart-container w-64 h-64 mx-auto ${dark ? 'bg-gray-900' : 'bg-white'}`;
            this.tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
            this.segments.forEach(segment => {
                segment.path.setAttribute("stroke", dark ? "#1f2937" : "white");
                segment.path.setAttribute("stroke-width", "0.7");
            });
            this.legendLabels.forEach(label => {
                label.className = `text-sm ${dark ? 'text-gray-300' : 'text-gray-700'}`;
            });
            this.legendContainer.className = `pie-chart-legend mt-4 w-full ${dark ? 'bg-gray-900' : 'bg-white'}`;
        }
    };
}
