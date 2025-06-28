// resources/js/components/chart-pie.js

/**
 * ダークモードが有効かどうかを判定します。
 * @returns {boolean}
 */
const isDarkMode = () => {
    return document.documentElement.classList.contains('dark') ||
           window.matchMedia('(prefers-color-scheme: dark)').matches;
};

/**
 * SVG要素を生成します。
 * @param {string} tag - SVGタグ名
 * @param {object} attributes - 属性のオブジェクト
 * @returns {SVGElement}
 */
const createSvgElement = (tag, attributes) => {
    const el = document.createElementNS("http://www.w3.org/2000/svg", tag);
    for (const key in attributes) {
        el.setAttribute(key, attributes[key]);
    }
    return el;
};

/**
 * Alpine.jsの円グラフコンポーネント
 * @param {object} options - チャートのオプション
 */
export default function chartPie(options) {
    return {
        // --- 状態管理 ---
        container: null, // チャートコンテナ
        legendContainer: null, // 凡例コンテナ
        tooltip: null, // ツールチップ
        svg: null, // SVG要素
        segments: [], // 円グラフのセグメント
        legendLabels: [], // 凡例のラベル
        darkModeObserver: null, // ダークモード監視

        // --- オプション ---
        ...options,

        /**
         * コンポーネントの初期化
         */
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

        /**
         * 基本的なDOM要素のセットアップ
         */
        setupBaseElements() {
            // 既存の要素をクリア
            while (this.container.firstChild) this.container.removeChild(this.container.firstChild);
            while (this.legendContainer.firstChild) this.legendContainer.removeChild(this.legendContainer.firstChild);

            // SVGコンテナを生成
            this.svg = createSvgElement("svg", {
                viewBox: "0 0 100 100",
                class: "w-full h-full max-w-md mx-auto"
            });
            this.container.appendChild(this.svg);
        },

        /**
         * 円グラフと凡例の描画
         */
        renderPieAndLegend() {
            const cx = 50;
            const cy = 50;
            const r = 40;
            let startAngle = -Math.PI / 2;

            const legend = document.createElement('div');
            legend.className = 'flex flex-wrap justify-center gap-4 py-2';

            this.data.forEach((value, index) => {
                if (value <= 0) return;

                // セグメントの角度を計算
                const angle = (value / this.total) * (Math.PI * 2);
                const endAngle = startAngle + angle;
                const x1 = cx + r * Math.cos(startAngle);
                const y1 = cy + r * Math.sin(startAngle);
                const x2 = cx + r * Math.cos(endAngle);
                const y2 = cy + r * Math.sin(endAngle);
                const largeArcFlag = angle > Math.PI ? 1 : 0;

                // SVGパスを生成
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

                // ツールチップイベント
                path.addEventListener('mouseenter', () => this.showTooltip(segment));
                path.addEventListener('mouseleave', () => this.hideTooltip(segment));

                startAngle = endAngle;

                // 凡例アイテムを生成
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

        /**
         * ツールチップを表示
         * @param {object} segment - 表示するセグメント
         */
        showTooltip(segment) {
            segment.path.setAttribute("opacity", "0.85");
            segment.path.setAttribute("stroke-width", "1.2");
            this.tooltip.textContent = `${segment.label}: ${segment.value.toLocaleString()} (${segment.percent}%)`;
        },

        /**
         * ツールチップを非表示
         * @param {object} segment - 非表示にするセグメント
         */
        hideTooltip(segment) {
            segment.path.setAttribute("opacity", "1");
            segment.path.setAttribute("stroke-width", "0.7");
            this.tooltip.textContent = '円グラフのセグメントにカーソルを合わせると詳細が表示されます';
        },

        /**
         * イベントリスナーのセットアップ
         */
        setupEventListeners() {
            this.darkModeObserver = new MutationObserver(() => this.updateDarkModeStyles());
            this.darkModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => this.updateDarkModeStyles());
        },

        /**
         * ダークモードのスタイル更新
         */
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
