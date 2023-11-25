<?php

use function Livewire\Volt\state;

state(['pref', 'area']);
?>

<div class="ml-3">
    @foreach(config('service') as $service_id => $service)
        <a href="/?pref={{ $pref }}&area={{ $area }}&service={{ $service_id }}"
           class="text-xs hover:text-indigo-500 hover:underline" wire:key="{{ $service_id }}" wire:navigate>{{ $service }}</a>
    @endforeach
</div>
