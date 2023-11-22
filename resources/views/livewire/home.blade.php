<?php

use App\Models\Area;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use function Livewire\Volt\computed;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('layouts.app');

state(['service' => '', 'pref' => '', 'area' => '', 'limit' => 100])->url();

$facilities = computed(function () {
    return Facility::when(filled($this->service), function (Builder $query) {
        $query->where('service_id', $this->service);
    })->when(filled($this->pref), function (Builder $query) {
        $query->where('pref_id', $this->pref);
    })->when(filled($this->area), function (Builder $query) {
        $query->where('area_id', $this->area);
    })->latest('updated_at')
        ->simplePaginate($this->limit)->withQueryString();
});

$areas = computed(function () {
    return Area::withCount(['facilities'])->when(filled($this->pref), function (Builder $query) {
        $query->where('pref_id', $this->pref);
    })->orderByDesc('facilities_count')->get();
});

?>

<div class="mx-1 sm:mx-10">
    <div>
        @include('layouts.header')

        <div>
            <form class="grid grid-cols-2 sm:grid-flow-col sm:grid-cols-auto gap-2">
                <x-select name="pref" wire:model.live="pref" wire:change="$set('area', '')">
                    <option value="">都道府県</option>
                    <hr>
                    @foreach(Pref::get() as $pref)
                        <option value="{{ $pref->id }}">{{ $pref->name }}</option>
                        @if(in_array($pref->id, [1, 7, 14, 23, 30, 35, 39]))
                            <hr>
                        @endif
                    @endforeach
                </x-select>

                <x-select name="area" wire:model.live="area" :disabled="blank($this->pref)">
                    <option value="">自治体</option>
                    <hr>
                    @foreach($this->areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->facilities_count }})</option>
                    @endforeach
                </x-select>

                <x-select name="service" wire:model.live="service">
                    <option value="">サービス</option>
                    <hr>
                    @foreach(Service::has('facilities')->withCount(['facilities' => function (Builder $query) {
                $query->when(filled($this->area), function (Builder $query) {
                    $query->where('area_id', $this->area);
                })->when(filled($this->pref), function (Builder $query) {
            $query->where('pref_id', $this->pref);
        });
            }])->get() as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->facilities_count }})
                        </option>
                    @endforeach
                </x-select>

                <x-select name="limit" wire:model.live="limit">
                    <option value="" disabled>表示件数</option>
                    <hr>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="5000">5000</option>
                </x-select>
            </form>
        </div>
    </div>

    <h2 class="my-6 text-4xl">
        {{ Pref::find($this->pref)?->name }}
        {{ Area::find($this->area)?->name }}
        {{ Service::find($this->service)?->name }}
    </h2>

    <div class="my-3">
        {{ $this->facilities->links() }}
    </div>

    <table class="table-auto w-full border-collapse border-2 border-indigo-500">
        <thead class="sticky top-0">
        <tr class="bg-indigo-400 border-b-2 border-indigo-500 divide-x-2 divide-solid divide-indigo-500">
            <th>サービス</th>
            <th>事業所名</th>
            <th>自治体</th>
            <th>運営法人</th>
            <th>URL</th>
            <th>WAM</th>
        </tr>
        </thead>
        <tbody>

        @foreach($this->facilities as $facility)
            <tr class="border border-indigo-500 divide-x divide-solid divide-indigo-500"
                wire:key="{{ $facility->id  }}">
                <td class="p-1">{{ $facility->service->name }}</td>
                <td class="p-1 font-bold"><a href="{{ route('facility', $facility) }}"
                                             class="text-indigo-500 hover:underline" wire:navigate>{{ $facility->name }}</a></td>
                <td class="p-1">{{ $facility->area->address }}</td>
                <td class="p-1">{{ $facility->company->name }}</td>
                <td class="p-1">@if(filled($facility->url))
                        <a href="{{ $facility->url }}" class="text-indigo-500 hover:underline" target="_blank">URL</a>
                    @endif</td>
                <td class="p-1"><a
                        href="https://www.google.com/search?q={{ rawurlencode($facility->name.' site:www.wam.go.jp/sfkohyoout/') }}"
                        class="text-indigo-500 hover:underline" target="_blank">検索</a></td>
            </tr>

        @endforeach

        </tbody>

    </table>

    <div class="my-3">
        {{ $this->facilities->links() }}
    </div>
</div>
