@props(['title' => null])

<div {{ $attributes->merge(['class' => 'chart-container my-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700']) }}>
    @if($title)
        <h4 class="text-center text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">{{ $title }}</h4>
    @endif

    {{ $slot }}
</div>
