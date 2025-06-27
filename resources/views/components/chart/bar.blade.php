<x-chart.base :title="$title">
    @props(['data', 'labels', 'maxValue' => null, 'barColor' => 'blue', 'title' => null])

    @php
        $maxValue = $maxValue ?? max($data);
    @endphp

    <div x-data="{
      data: {{ json_encode($data) }},
      labels: {{ json_encode($labels) }},
      maxValue: {{ $maxValue }}
    }">
      <div class="h-64 flex items-end space-x-2">
        <template x-for="(value, index) in data" :key="index">
          <div class="flex flex-col items-center">
            <div class="w-16 bg-{{ $barColor }}-500 hover:bg-{{ $barColor }}-700 transition-all"
                 :style="`height: ${value / maxValue * 100}%`"
                 @mouseover="$refs.tooltip.textContent = `${labels[index]}: ${value.toLocaleString()}施設`"
            ></div>
            <div class="text-xs rotate-45 origin-left mt-2" x-text="labels[index].substring(0,7)"></div>
          </div>
        </template>
      </div>
      <div x-ref="tooltip" class="text-sm mt-4 text-center">グラフにカーソルを合わせると詳細が表示されます</div>
    </div>
</x-chart.base>
