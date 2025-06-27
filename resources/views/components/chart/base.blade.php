@props(['title' => null])

<div {{ $attributes->merge(['class' => 'chart-container my-6 bg-gray-50 p-4 rounded-lg border border-gray-200']) }}>
    @if($title)
        <h4 class="text-center text-lg font-medium mb-4">{{ $title }}</h4>
    @endif

    {{ $slot }}
</div>
