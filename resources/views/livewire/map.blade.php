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

    <div class="text-sm my-2 py-2">
        自治体一覧。ページ内を検索してください。
    </div>

    @foreach($this->prefs as $pref)
        <div>
            <h2 class="text-3xl p-3 bg-indigo-300 border-2 border-indigo-500" id="{{ $pref->key }}">
                <a href="/?pref={{ $pref->id }}" wire:navigate>{{ $pref->name }}</a>
            </h2>
            <livewire:map-pref :pref="$pref" lazy="on-load"></livewire:map-pref>
        </div>

    @endforeach
</div>
