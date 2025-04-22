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

        <div>
            <span class="badge badge-soft badge-success">法人情報</span>
        </div>

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl text-primary">
            <ruby>
                {{ $company->name }}
                <rp>(</rp>
                <rt class="text-xs">{{ $company->name_kana }}</rt>
                <rp>)</rp>
            </ruby>
        </h2>

        <div class="border-base-300 w-full overflow-x-auto border border-gray-200">
            <table class="table">
                <tr>
                    <th class="text-base-content bg-base-200">法人番号</th>
                    <td>{{ $company->id }}</td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">住所</th>
                    <td>
                        {{ $company->area }}
                    </td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">URL</th>
                    <td>@if(filled($company->url))
                            <a href="{{ $company->url }}" class="link link-primary link-animated"
                               target="_blank" rel="nofollow">{{ Str::limit($company->url, 100) }}</a>
                        @endif</td>
                </tr>
{{--                <tr>--}}
{{--                    <th class="text-base-content bg-base-200">更新日</th>--}}
{{--                    <td>--}}
{{--                        <div class="text-xs text-pretty">--}}
{{--                            {{ config('wam.last_updated') }}時点のデータを表示しています。データは半年ごとに更新されるので最新の情報はWAMのページを検索して確認してください。--}}
{{--                        </div>--}}
{{--                    </td>--}}
{{--                </tr>--}}
            </table>
        </div>
    </div>

    <div class="mt-9 divider divider-start text-md">
        {{ $company->name }}の事業所
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>

    <div class="border-base-300 w-full border border-gray-200">
        <div class="overflow-x-auto">
            <table class="table table-md" id="list">
                <thead>
                <tr class="*:text-base-content *:bg-base-200">
                    <th>サービス</th>
                    <th>事業所名</th>
                    <th>自治体</th>
                </tr>
                </thead>
                <tbody>

                @foreach($this->facilities as $facility)
                    <tr wire:key="{{ $facility->id  }}">
                        <td>{{ $facility->service->name }}</td>
                        <td><a
                                href="{{ route('facility', $facility) }}"
                                class="font-bold link link-primary link-animated">{{ $facility->name }}</a></td>
                        <td class="break-auto">{{ $facility->area->address }}</td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>
</div>
