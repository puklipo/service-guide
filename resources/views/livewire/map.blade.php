<?php

use App\Models\Pref;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('layouts.app');

title('サイトマップ '.config('app.name'));

$prefs = computed(function () {
    return Pref::with('areas')->get();
});

?>

<div class="text-xl mx-1 sm:mx-10">
    @include('layouts.header')

    <h2 class="text-4xl my-6">サイトマップ</h2>

    <div class="text-sm my-2 py-2 px-2">
        自治体一覧。ページ内を検索してください。
    </div>

    @foreach($this->prefs as $pref)
        <div>
            <h2 class="text-3xl p-3 bg-indigo-400" id="{{ $pref->key }}"><a
                    href="/?pref={{ $pref->id }}" wire:navigate>{{ $pref->name }}</a></h2>
            <ul class="ml-10 list-disc">
                @foreach($pref->areas as $area)
                    <li class="my-1"><a href="/?pref={{ $pref->id }}&area={{ $area->id }}"
                                        class="text-indigo-500 underline" wire:navigate>{{ $area->name }}</a>
                    </li>
                    <ul class="ml-5">
                        @foreach(config('service') as $service_id => $service)
                            <a href="/?pref={{ $pref->id }}&area={{ $area->id }}&service={{ $service_id }}"
                               class="text-xs hover:text-indigo-500 hover:underline" wire:navigate>{{ $service }}</a>
                        @endforeach
                    </ul>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
