<?php

use App\Support\Markdown;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Str;

use function Livewire\Volt\{state};
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\title;
use function Livewire\Volt\{computed};

/**
 * 個別記事を表示するLivewire Voltコンポーネント
 */

layout('layouts.app');

state(['date', 'slug', 'file', 'title', 'description']);

mount(function (string $date, string $slug) {
    $this->file = resource_path('articles/'.$date.'/'.$slug.'.md');
    if (! file_exists($this->file)) {
        return to_route('home');
    }

    $matter = Markdown::matter($this->file);
    $this->title = data_get($matter, 'title', Str::of($slug)->replace('-', ' ')->title()->value());
    $this->description = data_get($matter, 'description', Str::of($this->title)->limit(100)->value());
});

$markdown = computed(function () {
    return Markdown::parse(File::get($this->file));
});

title(fn () => $this->title);

?>
<x-slot:description>{{ $description }}</x-slot>

<div class="mx-1 sm:mx-10">
    @include('layouts.header')

    <div class="prose max-w-4xl mx-6 lg:mx-auto my-12">
        <livewire:articles.date-select :date="$date"/>

        <article>
            {{ $this->markdown }}
        </article>

        <div class="my-6">
            <h2>{{ Carbon::createFromFormat('Ym', $date)->format('Y年m月') }}の記事</h2>
            <div class="not-prose">
                <livewire:articles.list :date="$date"/>
            </div>
        </div>
    </div>
</div>
