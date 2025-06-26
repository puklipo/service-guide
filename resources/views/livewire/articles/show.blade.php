<?php

use App\Support\Markdown;
use Illuminate\Support\Carbon;
use Spatie\YamlFrontMatter\YamlFrontMatter;

use function Livewire\Volt\{state};
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\title;

layout('layouts.app');

state(['date', 'title', 'description', 'body']);

mount(function (string $date, string $slug) {
    $file = resource_path('articles/'.$date.'/'.$slug.'.md');
    if (! file_exists($file)) {
        return to_route('home');
    }

    $document = YamlFrontMatter::parseFile($file);
    $this->title = $document->matter('title') ?? 'Article';
    $this->description = $document->matter('description') ?? 'No description provided.';
    $this->body = $document->body();
});

title(fn () => $this->title);

?>

<div class="mx-1 sm:mx-10">
    @include('layouts.header')

    <div class="prose max-w-4xl mx-6 lg:mx-auto my-12">
        <article class="prose">
            {{ Markdown::parse($body) }}
        </article>

        <div class="my-6">
            <h3>{{ Carbon::createFromFormat('Ym',$date)->format('Y年m月') }}の記事</h3>
        </div>
    </div>
</div>
