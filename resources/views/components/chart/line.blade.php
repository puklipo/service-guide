<x-chart.base>
    @props(['data', 'labels', 'maxValue' => null, 'lineColor' => 'blue'])

    @php
        $maxValue = $maxValue ?? max($data);
        $points = [];

        foreach ($data as $index => $value) {
            $points[] = ($index / (count($data) - 1)) * 100 . ',' . (100 - ($value / $maxValue * 100));
        }

        $pointsStr = implode(' ', $points);
    @endphp

    <div x-data="{
      data: {{ json_encode($data) }},
      labels: {{ json_encode($labels) }},
      maxValue: {{ $maxValue }},
      hoveredIndex: null
    }" class="relative">
      <div class="h-64 w-full">
        <svg class="w-full h-full">
          <polyline
            points="{{ $pointsStr }}"
            fill="none"
            stroke="rgb(59, 130, 246)"
            stroke-width="2"
            vector-effect="non-scaling-stroke"
            class="transform scale-x-[100%] scale-y-[100%]"
          />

          <template x-for="(value, index) in data" :key="index">
            <circle
              :cx="`${(index / (data.length - 1)) * 100}%`"
              :cy="`${100 - (value / maxValue * 100)}%`"
              r="4"
              :class="hoveredIndex === index ? 'fill-{{ $lineColor }}-700' : 'fill-{{ $lineColor }}-500'"
              @mouseover="hoveredIndex = index; $refs.tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}施設`"
              @mouseout="hoveredIndex = null"
            />
          </template>
        </svg>
      </div>

      <div class="flex justify-between mt-2">
        <template x-for="(label, index) in labels" :key="index">
          <div class="text-xs" x-text="label.substring(0,7)"></div>
        </template>
      </div>

      <div x-ref="tooltip" class="text-sm mt-4 text-center">グラフにポイントをホバーすると詳細が表示されます</div>
    </div>
</x-chart.base>
