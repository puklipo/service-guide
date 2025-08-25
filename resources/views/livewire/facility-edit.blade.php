<?php

use App\Models\Facility;

use App\Notifications\DraftCreated;
use Illuminate\Support\Facades\Notification;

use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state(['facility', 'show_draft', 'description_draft']);

mount(function (Facility $facility) {
    $this->facility = $facility;
    $this->description_draft = $facility->description_draft ?? ltrim(config('facility.description_default'));
});

$draft = function () {
    $this->facility->forceFill(['description_draft' => trim($this->description_draft)])->save();
    $this->dispatch('description-updated');

    Notification::route('mail', config('mail.admin.to'))
        ->notify(new DraftCreated($this->facility));
}
?>
<div class="my-3">
    @unless($show_draft)
        <x-secondary-button wire:click="$toggle('show_draft')">事業所情報を編集</x-secondary-button>
    @else
        <div>
            <ul>
                <li>追加の事業所情報を自由記入で入力できます。</li>
                <li>誰でも投稿可能ですが管理者が承認後に公開されます。</li>
            </ul>
            <form wire:submit="draft" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="description_draft" :value="__('事業所情報')"/>
                    <div
                        x-data="markdownEditor"
                        x-init="() => { setTimeout(() => init(), 100); }"
                        class="mt-1"
                    >
                        <div
                            x-ref="editor"
                            style="height: 300px;"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        ></div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('description_draft')"/>
                </div>

                <div class="flex items-center gap-4">
                    <x-secondary-button
                        wire:click="$toggle('show_draft')"
                        x-on:click="destroy()"
                    >
                        キャンセル
                    </x-secondary-button>

                    <x-primary-button>{{ __('下書きを投稿') }}</x-primary-button>

                    <x-action-message class="me-3" on="description-updated">
                        {{ __('投稿しました') }}
                    </x-action-message>
                </div>
            </form>
        </div>
    @endunless
</div>
