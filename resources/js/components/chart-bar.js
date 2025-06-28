// resources/js/components/chart-bar.js

// デフォルトのチャート設定
const CHART_DEFAULTS = {
    width: 800, // SVGの幅
    height: 400, // SVGの高さ
    paddingBottom: 60, // 下のパディング
    paddingLeft: 60, // 左のパディング
    paddingTop: 20, // 上のパディング
    paddingRight: 20, // 右のパディング
    barSpacing: 8, // 棒グラフの間隔
    gridCount: 5, // グリッド線の数
};

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
 * Alpine.jsの棒グラフコンポーネント
 * @param {object} options - チャートのオプション
 */
export default function chartBar(options) {
    return {
        // --- 状態管理 ---
        container: null, // チャートのコンテナ要素
        svg: null, // SVG要素
        tooltip: null, // ツールチップ要素
        rangeInfo: null, // 表示範囲情報要素
        barElements: [], // 棒グラフの要素
        labelElements: [], // ラベル要素
        gridLines: [], // グリッド線
        gridLabels: [], // グリッドのラベル
        yAxis: null, // Y軸
        xAxis: null, // X軸
        darkModeObserver: null, // ダークモード監視

        // --- オプション ---
        ...options,

        /**
         * コンポーネントの初期化
         */
        init() {
            this.container = this.$refs.container;
            if (!this.container) return;

            this.$nextTick(() => {
                this.setupBaseElements();
                this.renderGridAndAxes();
                this.renderBars();
                this.setupEventListeners();
                this.updateDarkModeStyles();
            });
        },

        /**
         * 基本的なDOM要素のセットアップ
         */
        setupBaseElements() {
            // 既存の要素をクリア
            while (this.container.firstChild) {
                this.container.removeChild(this.container.firstChild);
            }

            // 表示範囲情報を追加
            this.rangeInfo = document.createElement('div');
            this.rangeInfo.textContent = `表示範囲: ${this.displayMinValue.toLocaleString()} 〜 ${this.maxValue.toLocaleString()}`;
            this.container.appendChild(this.rangeInfo);

            // SVGコンテナを生成
            this.svg = createSvgElement("svg", {
                viewBox: `0 0 ${CHART_DEFAULTS.width} ${CHART_DEFAULTS.height}`
            });
            this.container.appendChild(this.svg);

            // ツールチップを生成
            this.tooltip = document.createElement('div');
            this.tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
            this.container.appendChild(this.tooltip);
        },

        /**
         * グリッド線と軸の描画
         */
        renderGridAndAxes() {
            // グリッド線とY軸ラベルを描画
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

            // Y軸とX軸を描画
            this.yAxis = createSvgElement("line", { x1: CHART_DEFAULTS.paddingLeft, y1: CHART_DEFAULTS.paddingTop, x2: CHART_DEFAULTS.paddingLeft, y2: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom });
            this.svg.appendChild(this.yAxis);
            this.xAxis = createSvgElement("line", { x1: CHART_DEFAULTS.paddingLeft, y1: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom, x2: CHART_DEFAULTS.width - CHART_DEFAULTS.paddingRight, y2: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom });
            this.svg.appendChild(this.xAxis);
        },

        /**
         * 棒グラフの描画
         */
        renderBars() {
            const shouldRotateLabels = this.data.length > 5;
            const labelRotationAngle = shouldRotateLabels ? -25 : 0;
            const labelYOffset = shouldRotateLabels ? 30 : 20;
            const chartWidth = CHART_DEFAULTS.width - CHART_DEFAULTS.paddingLeft - CHART_DEFAULTS.paddingRight;
            const totalBarSpace = chartWidth - (CHART_DEFAULTS.barSpacing * (this.data.length - 1));
            const barWidth = totalBarSpace / this.data.length;

            this.data.forEach((value, index) => {
                const barHeight = this.calculateBarHeight(value);
                const x = CHART_DEFAULTS.paddingLeft + (index * (barWidth + CHART_DEFAULTS.barSpacing));
                const y = CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - barHeight;

                // 棒を生成
                const bar = createSvgElement("rect", { x, y, width: barWidth, height: barHeight, rx: "2", class: "cursor-pointer transition-all duration-200" });
                this.barElements.push(bar);
                this.svg.appendChild(bar);

                // ツールチップイベント
                bar.addEventListener('mouseenter', () => {
                    bar.setAttribute("opacity", "0.8");
                    this.tooltip.textContent = `${this.labels[index]}: ${value.toLocaleString()}`;
                });
                bar.addEventListener('mouseleave', () => {
                    bar.setAttribute("opacity", "1");
                    this.tooltip.textContent = '詳細を表示するにはグラフにカーソルを合わせてください';
                });
                bar.addEventListener('touchstart', () => {
                    this.tooltip.textContent = `${this.labels[index]}: ${value.toLocaleString()}`;
                });

                // ラベルを生成
                const label = createSvgElement("text", { x: x + (barWidth / 2), y: CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom + labelYOffset, "font-size": "12" });
                if (shouldRotateLabels) {
                    label.setAttribute("transform", `rotate(${labelRotationAngle} ${x + (barWidth / 2)}, ${CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom + 10})`);
                    label.setAttribute("text-anchor", "end");
                } else {
                    label.setAttribute("text-anchor", "middle");
                }
                label.textContent = this.labels[index];
                this.labelElements.push(label);
                this.svg.appendChild(label);
            });
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
            this.container.className = `bar-chart-container w-full rounded-md ${dark ? 'bg-gray-900' : 'bg-white'}`;
            this.svg.setAttribute("class", "w-full h-64 bg-opacity-50");
            this.rangeInfo.className = `text-xs text-right w-full pr-2 opacity-70 mb-4 ${dark ? 'text-gray-400' : 'text-gray-500'}`;
            this.tooltip.className = `mt-4 text-center text-sm font-medium ${dark ? 'text-gray-300' : 'text-gray-600'}`;
            this.gridLines.forEach(line => line.setAttribute("stroke", dark ? "#374151" : "#f1f5f9"));
            this.yAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
            this.xAxis.setAttribute("stroke", dark ? "#4b5563" : "#cbd5e1");
            this.gridLabels.forEach(label => label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
            this.barElements.forEach(bar => bar.setAttribute("fill", dark ? "#3b82f6" : "#3b82f6"));
            this.labelElements.forEach(label => label.setAttribute("fill", dark ? "#9ca3af" : "#64748b"));
        },

        /**
         * 棒の高さを計算
         * @param {number} value - データ値
         * @returns {number}
         */
        calculateBarHeight(value) {
            if (this.adjustedDataRange === 0) return 0;
            return ((value - this.displayMinValue) / this.adjustedDataRange) * (CHART_DEFAULTS.height - CHART_DEFAULTS.paddingBottom - CHART_DEFAULTS.paddingTop);
        },
    };
}
