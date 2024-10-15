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

<div class="mx-1 sm:mx-10">
    @include('layouts.header')

    <h2 class="text-4xl my-6">サイトマップ</h2>

    <div class="text-sm my-2 py-2">
        自治体一覧。ページ内を検索してください。
    </div>

    @foreach($this->prefs as $pref)
        <div class="text-xl" wire:key="{{ $pref->id }}">
            <h2 class="text-3xl p-3 bg-primary/50 dark:bg-primary/90 border-2 border-primary" id="{{ $pref->key }}">
                <a href="/?pref={{ $pref->id }}">{{ $pref->name }}</a>
            </h2>
            <ul class="ml-6 list-disc">
                @foreach($pref->areas as $area)
                    <li class="my-1" wire:key="{{ $area->id }}">
                        <a href="/?pref={{ $pref->id }}&amp;area={{ $area->id }}"
                           class="link link-primary link-animated">{{ $area->name }}</a>
                    </li>

                @endforeach
            </ul>
        </div>

    @endforeach
</div>
