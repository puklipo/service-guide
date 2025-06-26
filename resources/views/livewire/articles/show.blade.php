<?php

use App\Support\Markdown;
use Illuminate\Support\Carbon;
use Spatie\YamlFrontMatter\YamlFrontMatter;

use function Livewire\Volt\{state};
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\title;
use function Livewire\Volt\{computed};

layout('layouts.app');

state(['date', 'slug', 'file', 'title', 'description']);

mount(function (string $date, string $slug) {
    $this->file = resource_path('articles/'.$date.'/'.$slug.'.md');
    if (! file_exists($this->file)) {
        return to_route('home');
    }

    $document = YamlFrontMatter::parseFile($this->file);
    $this->title = $document->matter('title');
    $this->description = $document->matter('description');
});

$document = computed(function () {
    return YamlFrontMatter::parseFile($this->file);
});

title(fn () => $this->title);

?>
<x-slot:description>{{ $description }}</x-slot>

<div class="mx-1 sm:mx-10">
    @include('layouts.header')

    <div class="prose max-w-4xl mx-6 lg:mx-auto my-12">
        <article>
            {{ Markdown::parse($this->document->body()) }}
        </article>

        <div class="my-6">
            <h2>{{ Carbon::createFromFormat('Ym',$date)->format('Y年m月') }}の記事</h2>
            <div class="not-prose">
                <livewire:articles.list :date="$date"/>
            </div>
        </div>

        <div class="my-6">
            <!-- 別の時期の同じ記事 -->
            <h3></h3>
        </div>
    </div>
</div>
