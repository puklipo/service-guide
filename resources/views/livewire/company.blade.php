<?php

use App\Models\Company;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('layouts.app');

state(['company']);

mount(function (Company $company) {
    $this->company = $company;
});

title(fn () => $this->company->name.' '.$this->company->area);

$facilities = computed(function () {
    return $this->company->facilities()->simplePaginate(10)->withQueryString();
});
?>
<div class="mx-1 sm:mx-10">
    <div>
        @include('layouts.header')

        @can('admin')
            <livewire:index-now :url="route('company', $company)"/>
        @endcan

        <div>法人情報</div>

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl bg-indigo-300 dark:bg-indigo-700 border-2 border-indigo-500">
            <ruby>
                {{ $company->name }}
                <rp>(</rp>
                <rt class="text-xs">{{ $company->name_kana }}</rt>
                <rp>)</rp>
            </ruby>
        </h2>

        <table class="table-auto w-full border-collapse border-2 border-indigo-500">
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">法人番号</th>
                <td class="p-1">{{ $company->id }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">住所</th>
                <td class="p-1">
                    {{ $company->area }}{{ $company->address }}
                    (<a href="https://www.google.com/maps/search/{{ rawurlencode($company->area.$company->address) }}"
                        target="_blank"
                        class="text-indigo-500 hover:underline" rel="nofollow">Googleマップ</a>)
                </td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300 dark:bg-indigo-700">URL</th>
                <td class="p-1">@if(filled($company->url))
                        <a href="{{ $company->url }}" class="text-indigo-500 hover:underline"
                           target="_blank" rel="nofollow">{{ Str::limit($company->url, 100) }}</a>
                    @endif</td>
            </tr>
        </table>
    </div>

    <hr class="my-10 border border-indigo-500">

    <div class="my-3 text-lg">
        {{ $company->name }}の事業所
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>

    <table class="table-auto w-full border-collapse border-2 border-indigo-500" id="list">
        <thead>
        <tr class="bg-indigo-300 dark:bg-indigo-700 border-b-2 border-indigo-500 divide-x-2 divide-solid divide-indigo-500">
            <th>サービス</th>
            <th>事業所名</th>
            <th>自治体</th>
        </tr>
        </thead>
        <tbody>

        @foreach($this->facilities as $facility)
            <tr class="border border-indigo-500 divide-x divide-solid divide-indigo-500"
                wire:key="{{ $facility->id  }}">
                <td class="p-1">{{ $facility->service->name }}</td>
                <td class="p-1 font-bold"><a
                            href="{{ route('facility', $facility) }}"
                            class="text-indigo-500 hover:underline">{{ $facility->name }}</a></td>
                <td class="p-1">{{ $facility->area->address }}</td>
            </tr>
        @endforeach
        </tbody>

    </table>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>
</div>
