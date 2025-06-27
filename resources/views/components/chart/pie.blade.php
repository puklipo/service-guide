<x-chart.base>
    @props(['data', 'labels', 'colors' => ['blue', 'green', 'red', 'yellow', 'indigo', 'purple', 'pink', 'gray']])

    @php
        $total = array_sum($data);
        $colorClasses = [];

        foreach ($colors as $index => $color) {
            $colorClasses[] = "bg-{$color}-500 hover:bg-{$color}-700";
        }
    @endphp

    <div x-data="{
      data: {{ json_encode($data) }},
      labels: {{ json_encode($labels) }},
      total: {{ $total }},
      colorClasses: {{ json_encode($colorClasses) }},
      hoveredIndex: null
    }">
      <div class="flex justify-center">
        <div class="relative w-64 h-64">
          <!-- 円グラフ -->
          <svg class="w-full h-full" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#eee" stroke-width="20" />

            @php
                $offset = 0;
            @endphp

            <template x-for="(value, index) in data" :key="index">
              @php
                  $offset += 10;
              @endphp
              <circle
                cx="50"
                cy="50"
                r="40"
                stroke-width="20"
                :stroke="hoveredIndex === index ? colorClasses[index % colorClasses.length].replace('bg-', 'rgb(') + ')' : colorClasses[index % colorClasses.length].replace('bg-', 'rgb(') + ')'"
                fill="transparent"
                :stroke-dasharray="`${value / total * 251.2} 251.2`"
                stroke-dashoffset="-{{ $offset }}"
                transform="rotate(-90 50 50)"
                @mouseover="hoveredIndex = index; $refs.tooltip.textContent = `${labels[index]}: ${value.toLocaleString()} (${(value / total * 100).toFixed(1)}%)`"
                @mouseout="hoveredIndex = null"
              ></circle>
            </template>
          </svg>
        </div>
      </div>

      <div class="flex flex-wrap justify-center mt-4 gap-2">
        <template x-for="(label, index) in labels" :key="index">
          <div class="flex items-center text-sm">
            <div :class="colorClasses[index % colorClasses.length]" class="w-4 h-4 mr-1"></div>
            <span x-text="label"></span>
          </div>
        </template>
      </div>

      <div x-ref="tooltip" class="text-sm mt-4 text-center">グラフのセグメントにホバーすると詳細が表示されます</div>
    </div>
</x-chart.base>
