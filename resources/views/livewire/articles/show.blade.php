<?php

use App\Articles\Article;

use App\Support\Markdown;

use function Livewire\Volt\{state};
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\title;

layout('layouts.app');

state(['title', 'description', 'body']);

mount(function (string $date, string $slug) {
    $file = resource_path('articles/'.$date.'/'.$slug.'.md');
    if (! file_exists($file)) {
        return to_route('home');
    }

    $article = new Article($file);
    $this->title = $article->matter('title') ?? 'Article';
    $this->description = $article->matter('description') ?? 'No description provided.';
    $this->body = $article->body();
});

title(fn () => $this->title);

?>

<div class="mx-1 sm:mx-10">
    @include('layouts.header')

    <div class="prose max-w-4xl mx-6 lg:mx-auto my-12">
        {{ Markdown::parse($body) }}
    </div>
</div>
