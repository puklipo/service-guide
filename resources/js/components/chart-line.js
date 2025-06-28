const CHART_DEFAULTS = {
    width: 800,
    height: 400,
    paddingBottom: 60,
    paddingLeft: 60,
    paddingTop: 20,
    paddingRight: 20,
    gridCount: 5,
};

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

export default function chartLine(options) {
    return {
        // State
        container: null,
        svg: null,
        tooltip: null,
        rangeInfo: null,
        gridLines: [],
        gridLabels: [],
        circles: [],
        axisLabels: [],
        path: null,
        yAxis: null,
        xAxis: null,
        darkModeObserver: null,

        // Options
        ...options,

        init() {
            this.container = this.$refs.container;
            if (!this.container) return;

            this.$nextTick(() => {
                this.setupBaseElements();
                this.renderGridAndAxes();
                this.renderLineAndPoints();
                this.setupEventListeners();
                this.updateChartColors();
            });
        },

        setupBaseElements() {
            while (this.container.firstChild) {
                this.container.removeChild(this.container.firstChild);
            }

            this.rangeInfo = document.createElement('div');
            this.rangeInfo.textContent = `表示範囲: ${this.displayMinValue.toLocaleString()} 〜 ${this.maxValue.toLocaleString()}`;
            this.container.appendChild(this.rangeInfo);

            this.svg = createSvgElement("svg", {
                viewBox: `0 0 ${CHART_DEFAULTS.width} ${CHART_DEFAULTS.height}`
            });
            this.container.appendChild(this.svg);

            this.tooltip = document.createElement('div');
            this.tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
            this.container.appendChild(this.tooltip);
        },

        renderGridAndAxes() {
            for (let i = 0; i < CHART_DEFAULTS.gridCount; i++) {
                const ratio = i / (CHART_DEFAULTS.gridCount - 1);
                const y = CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - (ratio * (CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - CHART_DEFAULTS.paddingTop));

                const gridLine = createSvgElement("line", { x1: CHART_DEFAULTS.paddingLeft, y1: y, x2: CHART_DEFAULTS.width - CHART_DEFAULTS.paddingRight, y2: y, "stroke-width": "1" });
                this.gridLines.push(gridLine);
                this.svg.appendChild(gridLine);

                const yValue = this.displayMinValue + (this.adjustedDataRange * ratio);
                const valueLabel = createSvgElement("text", { x: CHART_DEFAULTS.paddingLeft - 10, y: y + 4, "text-anchor": "end", "font-size": "12", class: "text-xs" });
                valueLabel.textContent = Math.round(yValue).toLocaleString();
                this.gridLabels.push(valueLabel);
                this.svg.appendChild(valueLabel);
            }

            this.yAxis = createSvgElement("line", { x1: CHART_DEFAULTS.paddingLeft, y1: CHART_DEFAULTS.paddingTop, x2: CHART_DEFAULTS.paddingLeft, y2: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom });
            this.svg.appendChild(this.yAxis);
            this.xAxis = createSvgElement("line", { x1: CHART_DEFAULTS.paddingLeft, y1: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom, x2: CHART_DEFAULTS.width - CHART_DEFAULTS.paddingRight, y2: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom });
            this.svg.appendChild(this.xAxis);
        },

        renderLineAndPoints() {
            const points = this.calculatePoints();
            const shouldRotateLabels = this.data.length > 5;
            const labelRotationAngle = shouldRotateLabels ? -25 : 0;
            const labelYOffset = shouldRotateLabels ? 30 : 15;

            this.path = createSvgElement("path", { d: this.getPath(points), fill: "none", "stroke-width": "4", class: "transition-all duration-300" });
            this.svg.appendChild(this.path);

            points.forEach(point => {
                const circle = createSvgElement("circle", { cx: point.x, cy: point.y, r: "6", "stroke-width": "2", class: "cursor-pointer transition-all duration-200" });
                this.circles.push(circle);
                this.svg.appendChild(circle);

                circle.addEventListener('mouseenter', () => {
                    circle.setAttribute("r", "8");
                    this.tooltip.textContent = `${point.label}: ${point.value.toLocaleString()}`;
                });
                circle.addEventListener('mouseleave', () => {
                    circle.setAttribute("r", "6");
                    this.tooltip.textContent = 'グラフのポイントにカーソルを合わせると詳細が表示されます';
                });

                const text = createSvgElement("text", { x: point.x, y: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom + labelYOffset, "font-size": "12", class: "text-sm" });
                if (shouldRotateLabels) {
                    text.setAttribute("transform", `rotate(${labelRotationAngle} ${point.x}, ${CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom + 10})`);
                    text.setAttribute("text-anchor", "end");
                } else {
                    text.setAttribute("text-anchor", "middle");
                }
                text.textContent = point.label;
                this.axisLabels.push(text);
                this.svg.appendChild(text);
            });
        },

        setupEventListeners() {
            this.darkModeObserver = new MutationObserver(() => this.updateChartColors());
            this.darkModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => this.updateChartColors());
        },

        updateChartColors() {
            const dark = isDarkMode();
            this.container.className = `line-chart-container w-full rounded-md ${dark ? 'bg-gray-900' : 'bg-white'}`;
            this.svg.setAttribute("class", "w-full h-64 bg-opacity-50");
            this.rangeInfo.className = `text-xs text-right w-full pr-2 opacity-70 mb-4 ${dark ? 'text-gray-400' : 'text-gray-500'}`;
            this.tooltip.className = `text-center text-sm mt-3 font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
            this.gridLines.forEach(line => line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"));
            this.gridLabels.forEach(label => label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
            this.xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
            this.yAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
            this.path.setAttribute("stroke", dark ? "#3b82f6" : "#3b82f6");
            this.circles.forEach(circle => {
                circle.setAttribute("fill", dark ? "#3b82f6" : "#3b82f6");
                circle.setAttribute("stroke", dark ? "#1e293b" : "#ffffff");
            });
            this.axisLabels.forEach(text => text.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
        },

        calculatePoints() {
            const points = [];
            if (this.data.length <= 1) {
                const x = CHART_DEFAULTS.paddingLeft + (CHART_DEFAULTS.width - CHART_DEFAULTS.paddingLeft - CHART_DEFAULTS.paddingRight) / 2;
                const y = CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - ((this.data[0] - this.displayMinValue) * ((CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - CHART_DEFAULTS.paddingTop) / this.adjustedDataRange));
                points.push({ x, y, value: this.data[0], label: this.labels[0] });
                return points;
            }

            const xStep = (CHART_DEFAULTS.width - CHART_DEFAULTS.paddingLeft - CHART_DEFAULTS.paddingRight) / (this.data.length - 1);
            const yScale = this.adjustedDataRange === 0 ? 0 : (CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - CHART_DEFAULTS.paddingTop) / this.adjustedDataRange;

            for (let i = 0; i < this.data.length; i++) {
                const x = CHART_DEFAULTS.paddingLeft + i * xStep;
                const y = CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - ((this.data[i] - this.displayMinValue) * yScale);
                points.push({ x, y, value: this.data[i], label: this.labels[i] });
            }
            return points;
        },

        getPath(points) {
            if (points.length < 2) return '';
            let path = `M ${points[0].x} ${points[0].y}`;
            for (let i = 1; i < points.length; i++) {
                path += ` L ${points[i].x} ${points[i].y}`;
            }
            return path;
        }
    };
}
