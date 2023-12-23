<?php

use App\Models\Facility;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('layouts.app');

state('facility');

mount(function (Facility $facility) {
    $this->facility = $facility;
});

title(fn () => $this->facility->name);

$facilities = computed(function () {
    return $this->facility->area
        ->facilities()
        ->where('service_id', $this->facility->service_id)
        ->latest()
        ->simplePaginate(10)
        ->withQueryString();
});
?>
<div class="mx-1 sm:mx-10">
    <div>
        @include('layouts.header')

        <div>事業所情報</div>

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl bg-indigo-300 dark:bg-indigo-700 border-2 border-indigo-500">
            <ruby>
                {{ $facility->name ?? '' }}
                <rp>(</rp>
                <rt class="text-xs">{{ $facility->name_kana }}</rt>
                <rp>)</rp>
            </ruby>
        </h2>

        <table class="table-auto w-full border-collapse border-2 border-indigo-500">
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">サービス</th>
                <td class="p-1">{{ $facility->service->name }}@if($facility->service->id === 33)
                        (<a href="https://grouphome.guide/home/{{ $facility->no }}"
                            class="text-indigo-500 hover:underline" target="_blank">グループホームガイドで調べる</a>)
                    @endif</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">住所</th>
                <td class="p-1">
                    {{ $facility->area->address }}{{ $facility->address }}
                    (<a href="https://www.google.com/maps/search/{{ rawurlencode($facility->area->address.$facility->address) }}"
                        target="_blank"
                        class="text-indigo-500 hover:underline" rel="nofollow">Googleマップ</a>)
                </td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">事業所番号</th>
                <td class="p-1">{{ $facility->no }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">運営法人</th>
                <td class="p-1">
                    <ruby>
                        <a href="{{ route('company', $facility->company) }}"
                           class="hover:text-indigo-500 hover:underline"
                           wire:navigate>{{ $facility->company->name }}</a>
                        <rp>(</rp>
                        <rt class="text-xs">{{ $facility->company->name_kana }}</rt>
                        <rp>)</rp>
                    </ruby>
                    <div class="text-sm">{{ $facility->company->area }}{{ $facility->company->address }}</div>
                </td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">URL</th>
                <td class="p-1">@if(filled($facility->url))
                        <a href="{{ $facility->url }}" class="text-indigo-500 hover:underline"
                           target="_blank" rel="nofollow">{{ Str::limit($facility->url, 100) }}</a>
                    @endif</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">WAM</th>
                <td class="p-1">
                    <a href="https://www.google.com/search?q={{ rawurlencode($facility->name.' site:www.wam.go.jp/sfkohyoout/') }}"
                       class="text-indigo-500 hover:underline" target="_blank" rel="nofollow">Google検索</a> <a
                        href="https://www.bing.com/search?q={{ rawurlencode($facility->name.' site:www.wam.go.jp/sfkohyoout/') }}"
                        class="text-indigo-500 hover:underline" target="_blank" rel="nofollow">Bing検索</a></td>
            </tr>
        </table>
    </div>

    @if(filled($facility->description))
        <div class="p-3 border border-2 border-indigo-500 prose prose-indigo dark:prose-invert max-w-none">
            {{ \App\Support\Markdown::escape($facility->description) }}
        </div>
    @endif

    <livewire:facility-edit :$facility/>

    @can('admin')
        <livewire:facility-admin :$facility/>
    @endcan

    <x-json-ld.facility-place :$facility/>

    <hr class="my-10 border border-indigo-500">

    <div class="my-3">
        {{ $facility->area->address  ?? '' }}の{{ $facility->service->name  ?? '' }}
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#area']) }}
    </div>

    <table class="table-auto w-full border-collapse border-2 border-indigo-500" id="area">
        <thead>
        <tr class="bg-indigo-300 dark:bg-indigo-700 border-b-2 border-indigo-500 divide-x-2 divide-solid divide-indigo-500">
            <th>事業所名</th>
            <th>運営法人</th>
        </tr>
        </thead>
        <tbody>

        @foreach($this->facilities as $facility)
            <tr class="border border-indigo-500 divide-x divide-solid divide-indigo-500"
                wire:key="{{ $facility->id  }}">
                <td class="p-1 font-bold"><a
                        href="{{ route('facility', ['service' => $facility->service, 'facility' => $facility]) }}"
                        class="text-indigo-500 hover:underline" wire:navigate>{{ $facility->name }}</a></td>
                <td class="p-1"><a href="{{ route('company', $facility->company) }}"
                                   class="hover:text-indigo-500 hover:underline"
                                   wire:navigate>{{ $facility->company->name }}</a></td>
            </tr>
        @endforeach
        </tbody>

    </table>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#area']) }}
    </div>
</div>
