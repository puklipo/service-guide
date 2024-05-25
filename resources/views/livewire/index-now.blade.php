<?php

use App\Support\IndexNow;

use function Livewire\Volt\{state};

state('url');

$submit = function () {
    $this->authorize('admin');
    if (app()->isProduction()) {
        IndexNow::submit($this->url);
    } else {
        info('IndexNow: '.$this->url);
    }
    $this->dispatch('index-now-submit');
}
?>

<div class="mb-3">
    <div class="flex items-center gap-4">
        <x-secondary-button wire:click="submit">IndexNow</x-secondary-button>

        <x-action-message class="me-3" on="index-now-submit">
            {{ __('送信しました') }}
        </x-action-message>
    </div>
</div>
