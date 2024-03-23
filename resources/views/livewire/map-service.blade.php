<?php

use function Livewire\Volt\state;

state(['pref', 'area']);
?>

<div class="ml-3 mb-3">
    <details class="hover:*:text-indigo-500 hover:*:underline">
        <summary>サービスを表示</summary>
        @foreach(config('service') as $service_id => $service)
            <a href="/?pref={{ $pref }}&amp;area={{ $area }}&amp;service={{ $service_id }}"
               class="text-sm" wire:key="{{ $service_id }}">{{ $service }}</a>
        @endforeach
    </details>
</div>
