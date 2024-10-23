<?php

use App\Models\Facility;
use Illuminate\Http\Request;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('layouts.app');

state('facility');

mount(function (Request $request, Facility $facility) {
    if ($request->has('service')) {
        return to_route('facility', $facility, 308);
    }

    $this->facility = $facility;
});

title(fn () => $this->facility->name.' ('.$this->facility->service->name.') '.' - '.$this->facility->area->address);

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

        @can('admin')
            <livewire:index-now :url="route('facility', $facility)"/>
        @endcan

        <div>
            <span class="badge badge-soft badge-accent">事業所情報</span>
        </div>

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl text-primary">
            <ruby>
                {{ $facility->name ?? '' }}
                <rp>(</rp>
                <rt class="text-xs">{{ $facility->name_kana }}</rt>
                <rp>)</rp>
            </ruby>
        </h2>

        <div class="border-base-300 w-full overflow-x-auto border">
            <table class="table">
                <tr>
                    <th class="text-base-content bg-base-200">サービス</th>
                    <td>{{ $facility->service->name }}@if($facility->service->id === 33)
                            (<a href="https://grouphome.guide/home/{{ $facility->no }}"
                                class="link link-primary link-animated" target="_blank">グループホームガイドで調べる</a>
                            )
                        @endif</td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">住所</th>
                    <td>
                        {{ $facility->area->address }}
                    </td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">事業所番号</th>
                    <td>{{ $facility->no }}</td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">運営法人</th>
                    <td>
                        <ruby>
                            <a href="{{ route('company', $facility->company) }}"
                               class="link link-primary link-animated">{{ $facility->company->name }}</a>
                            <rp>(</rp>
                            <rt class="text-xs">{{ $facility->company->name_kana }}</rt>
                            <rp>)</rp>
                        </ruby>
                        <div class="text-sm">{{ $facility->company->area }}</div>
                    </td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">URL</th>
                    <td>@if(filled($facility->url))
                            <a href="{{ $facility->url }}" class="link link-primary link-animated"
                               target="_blank" rel="nofollow">{{ Str::limit($facility->url, 100) }}</a>
                        @endif</td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">WAM</th>
                    <td>
                        <a href="https://www.google.com/search?q={{ rawurlencode($facility->name.' site:www.wam.go.jp/sfkohyoout/') }}"
                           class="link link-primary link-animated" target="_blank" rel="nofollow">Google検索</a> <a
                            href="https://www.bing.com/search?q={{ rawurlencode($facility->name.' site:www.wam.go.jp/sfkohyoout/') }}"
                            class="link link-primary link-animated" target="_blank" rel="nofollow">Bing検索</a></td>
                </tr>
                <tr>
                    <th class="text-base-content bg-base-200">基本情報更新日</th>
                    <td>
                        <div class="text-xs text-pretty">
                            {{ config('wam.last_updated') }}時点のデータを表示しています。最新の情報はWAMのページを検索して確認してください。
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if(filled($facility->description))
        <div class="p-3 border border-base-300 prose prose-indigo dark:prose-invert max-w-none break-auto">
            {{ \App\Support\Markdown::escape($facility->description) }}
        </div>
    @endif

    <livewire:facility-edit :$facility/>

    @can('admin')
        <livewire:facility-admin :$facility/>
    @endcan

    <div class="mt-9 divider divider-start text-md">
        {{ $facility->area->address  ?? '' }}の{{ $facility->service->name  ?? '' }}
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#area']) }}
    </div>

    <div class="border-base-300 w-full border">
        <div class="overflow-x-auto">
            <table class="table table-md" id="area">
                <thead>
                <tr class="*:text-base-content *:bg-base-200">
                    <th>事業所名</th>
                    <th>運営法人</th>
                </tr>
                </thead>
                <tbody>

                @foreach($this->facilities as $facility)
                    <tr wire:key="{{ $facility->id }}">
                        <td><a
                                href="{{ route('facility', $facility) }}"
                                class="font-bold link link-primary link-animated">{{ $facility->name }}</a></td>
                        <td><a href="{{ route('company', $facility->company) }}"
                               class="link link-primary link-animated">{{ $facility->company->name }}</a></td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#area']) }}
    </div>
</div>
