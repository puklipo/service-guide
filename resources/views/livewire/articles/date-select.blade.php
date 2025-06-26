<?php

use function Livewire\Volt\{mount, state, computed};
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

/**
 * Livewire Voltコンポーネント。
 * 渡された年月(date)を現在として他の年月に切り換える。
 *
 * 切り換えられるdateは`resources/articles`内の`YYYYMM`形式のディレクトリ名。202111から最新まで。
 * 個別記事`show.blade.php`の<article>の上に設置。
 */

state(['date', 'slug']);

state(['selectedDate' => fn () => $this->date]);

mount(function () {
    $this->slug = basename(url()->current());
});

$dates = computed(function () {
    $directories = File::directories(resource_path('articles'));

    return collect($directories)
        ->map(fn ($dir) => basename($dir))
        ->filter(fn ($name) => preg_match('/^\d{6}$/', $name))
        ->sortDesc()
        ->values();
});

$formatDate = function ($dateString) {
    try {
        return Carbon::createFromFormat('Ym', $dateString)->format('Y年m月');
    } catch (\Exception $e) {
        return $dateString;
    }
};

$changeDate = function () {
    if ($this->selectedDate) {
        return $this->redirect(route('articles.show', ['date' => $this->selectedDate, 'slug' => $this->slug]), navigate: true);
    }
};

?>

<div>
    <div class="mb-6 flex justify-end">
        <select class="select w-fit " wire:model.live="selectedDate" wire:change="changeDate">
            @foreach ($this->dates as $d)
                <option value="{{ $d }}">
                    {{ $this->formatDate($d) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
