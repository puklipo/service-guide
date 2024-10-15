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

title(fn () => $this->company->name.' - '.$this->company->area);

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

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl bg-primary/50 dark:bg-primary/90 border-2 border-primary">
            <ruby>
                {{ $company->name }}
                <rp>(</rp>
                <rt class="text-xs">{{ $company->name_kana }}</rt>
                <rp>)</rp>
            </ruby>
        </h2>

        <table class="table-auto w-full border-collapse border-2 border-primary">
            <tr class="border border-primary">
                <th class="bg-primary/50 dark:bg-primary/90">法人番号</th>
                <td class="p-1">{{ $company->id }}</td>
            </tr>
            <tr class="border border-primary">
                <th class="bg-primary/50 dark:bg-primary/90">住所</th>
                <td class="p-1">
                    {{ $company->area }}{{ $company->address }}
                    (<a href="https://www.google.com/maps/search/{{ rawurlencode($company->area.$company->address) }}"
                        target="_blank"
                        class="link link-primary link-animated" rel="nofollow">Googleマップ</a>)
                </td>
            </tr>
            <tr class="border border-primary">
                <th class="bg-primary/50 dark:bg-primary/90">URL</th>
                <td class="p-1">@if(filled($company->url))
                        <a href="{{ $company->url }}" class="link link-primary link-animated"
                           target="_blank" rel="nofollow">{{ Str::limit($company->url, 100) }}</a>
                    @endif</td>
            </tr>
        </table>
    </div>

    <hr class="my-10 border border-primary">

    <div class="my-3 text-lg">
        {{ $company->name }}の事業所
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>

    <table class="table-auto w-full border-collapse border-2 border-primary" id="list">
        <thead>
        <tr class="bg-primary/50 dark:bg-primary/90 border-b-2 border-primary divide-x-2 divide-solid divide-primary">
            <th>サービス</th>
            <th>事業所名</th>
            <th>自治体</th>
        </tr>
        </thead>
        <tbody>

        @foreach($this->facilities as $facility)
            <tr class="border border-primary divide-x divide-solid divide-primary"
                wire:key="{{ $facility->id  }}">
                <td class="p-1">{{ $facility->service->name }}</td>
                <td class="p-1 font-bold"><a
                            href="{{ route('facility', $facility) }}"
                            class="link link-primary link-animated">{{ $facility->name }}</a></td>
                <td class="p-1">{{ $facility->area->address }}</td>
            </tr>
        @endforeach
        </tbody>

    </table>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>
</div>
