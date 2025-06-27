<?php

use App\Support\Markdown;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use function Livewire\Volt\{state, computed};

/**
 * Livewire Voltコンポーネント。
 * 渡された年月(date)の記事一覧を表示するためのコンポーネント。
 *
 * dateが202503ならresources/articles/202503内のmarkdownファイルを読み込みタイトルとdescriptionを取得。
 *
 * slugはmarkdownファイル名から取得。
 *
 * リンク先は`route('articles.show', ['date' => $date, 'slug' => $slug])`。
 *
 * レスポンシブ。スマホでは1列6行。PCでは3列2行。
 */

state(['date']);

$articles = computed(function () {
    $articles = [];
    $path = resource_path("articles/$this->date");

    if (! File::isDirectory($path)) {
        return [];
    }

    foreach (File::files($path) as $file) {
        if (Str::startsWith(basename($file), '0') && Str::endsWith($file, '.md') && File::exists($file)) {
            $slug = Str::of($file)->basename()->chopEnd('.md')->value();

            $matter = Markdown::matter($file);
            $title = data_get($matter, 'title', Str::of($slug)->replace('-', ' ')->title()->value());
            $description = data_get($matter, 'description', Str::of($title)->limit(100)->value());

            $articles[] = [
                'slug' => $slug,
                'title' => trim($title),
                'description' => trim($description),
            ];
        }
    }

    return $articles;
});

?>

<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($this->articles as $article)
            <a href="{{ route('articles.show', ['date' => $date, 'slug' => $article['slug']]) }}"
               class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $article['title'] }}</h3>
                <p class="font-normal text-xs text-gray-500 dark:text-gray-400">{{ $article['description'] }}</p>
            </a>
        @endforeach
    </div>
</div>
