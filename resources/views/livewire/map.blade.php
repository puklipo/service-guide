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

    <div id="scrollspy-scrollable-parent">
        <div class="grid grid-cols-5">
            <div class="col-span-2 sm:col-span-1 vertical-scrollbar max-h-96">
                <ul class="text-sm leading-6"
                    data-scrollspy="#scrollspy"
                    data-scrollspy-scrollable-parent="#scrollspy-scrollable-parent">

                    @foreach($this->prefs as $pref)
                        <li class="hover:bg-base-200/50">
                            <a href="#{{ $pref->key }}"
                               class="text-base-content/80 hover:text-base-content/90 scrollspy-active:text-primary-content scrollspy-active:bg-primary block rounded-t-md p-2 py-1.5 font-medium">
                                {{ $pref->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-span-3 sm:col-span-4 vertical-scrollbar max-h-96">
                <div id="scrollspy" class="space-y-4 pe-1">
                    @foreach($this->prefs as $pref)
                        <div wire:key="{{ $pref->id }}" id="{{ $pref->key }}">
                            <h2 class="text-3xl p-3 mb-3 bg-primary text-primary-content">
                                <a href="/?pref={{ $pref->id }}">{{ $pref->name }}</a>
                            </h2>
                            <ul class="ml-6 list-disc list-inside">
                                @foreach($pref->areas as $area)
                                    <li class="mb-2" wire:key="{{ $area->id }}">
                                        <a href="/?pref={{ $pref->id }}&amp;area={{ $area->id }}"
                                           class="link link-primary link-animated">{{ $area->name }}</a>
                                    </li>

                                @endforeach
                            </ul>
                        </div>

                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
