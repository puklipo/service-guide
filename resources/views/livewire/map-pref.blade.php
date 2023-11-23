<?php

use function Livewire\Volt\placeholder;
use function Livewire\Volt\state;

state('pref');

placeholder('<div class="py-3 text-sm text-gray-400">読み込み中...</div>');
?>

<ul class="ml-6 list-disc">
    @foreach($pref->areas as $area)
        <li class="my-1">
            <a href="/?pref={{ $pref->id }}&area={{ $area->id }}"
               class="text-indigo-500 underline" wire:navigate>{{ $area->name }}</a>
        </li>
        <ul class="ml-3">
            @foreach(config('service') as $service_id => $service)
                <a href="/?pref={{ $pref->id }}&area={{ $area->id }}&service={{ $service_id }}"
                   class="text-xs hover:text-indigo-500 hover:underline" wire:navigate>{{ $service }}</a>
            @endforeach
        </ul>
    @endforeach
</ul>
