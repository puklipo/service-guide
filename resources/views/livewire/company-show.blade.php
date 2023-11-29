<div class="mx-1 sm:mx-10">
    <div>
        @include('layouts.header')

        <div>法人情報</div>

        <h2 class="my-3 pt-6 pb-3 px-3 text-4xl bg-indigo-300 border-2 border-indigo-500">
            <ruby>
                {{ $company->name }}
                <rp>(</rp><rt class="text-xs">{{ $company->name_kana }}</rt><rp>)</rp>
            </ruby>
        </h2>

        <table class="table-auto w-full border-collapse border-2 border-indigo-500">
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">法人番号</th>
                <td class="p-1">{{ $company->id }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">住所</th>
                <td class="p-1">{{ $company->area }}{{ $company->address }}</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">電話番号</th>
                <td class="p-1"><a href="tel:{{ $company->tel }}" class="hover:text-indigo-500 hover:underline" title="電話番号が間違ってる場合は問い合わせフォームから連絡してください">{{ $company->tel }}</a></td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">URL</th>
                <td class="p-1">@if(filled($company->url))
                        <a href="{{ $company->url }}" class="text-indigo-500 hover:underline" target="_blank">{{ Str::limit($company->url, 100) }}</a>
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
        <tr class="bg-indigo-300 border-b-2 border-indigo-500 divide-x-2 divide-solid divide-indigo-500">
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
                <td class="p-1 font-bold"><a href="{{ route('facility', ['service' => $facility->service, 'facility' => $facility]) }}"
                                             class="text-indigo-500 hover:underline" wire:navigate>{{ $facility->name }}</a></td>
                <td class="p-1">{{ $facility->area->address }}</td>
            </tr>
        @endforeach
        </tbody>

    </table>

    <div class="my-3">
        {{ $this->facilities->links(data: ['scrollTo' => '#list']) }}
    </div>
</div>
