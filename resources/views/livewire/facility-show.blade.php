<div class="mx-1 sm:mx-10">
    <div>
        @include('layouts.header')

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl bg-indigo-300 border-2 border-indigo-500">
            <ruby>
            {{ $facility->name ?? '' }}
                <rp>(</rp><rt class="text-xs">{{ $facility->name_kana }}</rt><rp>)</rp>
            </ruby>
        </h2>

        <table class="table-auto w-full border-collapse border-2 border-indigo-500">
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">サービス</th>
                <td class="p-1">{{ $facility->service->name }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">住所</th>
                <td class="p-1">{{ $facility->area->address }}{{ $facility->address }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">事業所番号</th>
                <td class="p-1">{{ $facility->no }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">運営法人</th>
                <td class="p-1">
                    <ruby>{{ $facility->company->name }}
                        <rp>(</rp><rt class="text-xs">{{ $facility->company->name_kana }}</rt><rp>)</rp>
                    </ruby>
                    <div class="text-sm">{{ $facility->company->area }}{{ $facility->company->address }}</div>
                </td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">URL</th>
                <td class="p-1">@if(filled($facility->url))
                        <a href="{{ $facility->url }}" class="text-indigo-500 hover:underline" target="_blank">{{ $facility->url }}</a>
                    @endif</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">WAM</th>
                <td class="p-1">
                    <a href="https://www.google.com/search?q={{ rawurlencode($facility->name.' site:www.wam.go.jp/sfkohyoout/') }}"
                        class="text-indigo-500 hover:underline" target="_blank">Google検索</a></td>
            </tr>
        </table>
    </div>

    <hr class="my-10 border border-indigo-500">

    <div class="my-3">
        {{ $facility->area->address  ?? '' }}の{{ $facility->service->name  ?? '' }}
    </div>

    <div class="my-3">
        {{ $this->area_facilities->links(data: ['scrollTo' => '#area']) }}
    </div>

    <table class="table-auto w-full border-collapse border-2 border-indigo-500" id="area">
        <thead>
        <tr class="bg-indigo-300 border-b-2 border-indigo-500 divide-x-2 divide-solid divide-indigo-500">
            <th>事業所名</th>
            <th>運営法人</th>
        </tr>
        </thead>
        <tbody>

        @foreach($this->area_facilities as $facility)
            <tr class="border border-indigo-500 divide-x divide-solid divide-indigo-500"
                wire:key="{{ $facility->id  }}">
                <td class="p-1 font-bold"><a href="{{ route('facility', $facility) }}"
                                             class="text-indigo-500 hover:underline" wire:navigate>{{ $facility->name }}</a></td>
                <td class="p-1">{{ $facility->company->name }}</td>
            </tr>
        @endforeach
        </tbody>

    </table>

    <div class="my-3">
        {{ $this->area_facilities->links(data: ['scrollTo' => '#area']) }}
    </div>
</div>
