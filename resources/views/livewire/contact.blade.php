<?php

use App\Livewire\Forms\ContactForm;

use function Livewire\Volt\{form, state};
use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('layouts.app');

state(['name', 'email', 'content']);

form(ContactForm::class);

title('問い合わせ '.config('app.name'));

$sendmail = function () {
    $this->form->submit();
}
?>
<div class="mx-1 sm:mx-10">
    @include('layouts.header')

    <h2 class="text-4xl my-6">問い合わせ</h2>

    <div class="text-md my-2 py-2 px-2">
        閉鎖済み事業所の削除依頼などはこちらから連絡してください。削除依頼時は当サイト内の事業所のURLと事業所番号を記入してください。情報の修正はWAM
        NET側を修正してください。
    </div>

    <div class="px-2">
        @if(session()->has('mail_success'))
            <div class="font-bold text-3xl">送信しました。</div>
        @else
            <form wire:submit="sendmail" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="name" :value="__('名前')"/>
                    <x-text-input wire:model="form.name" id="name" name="name" type="text" class="mt-1 block w-full" required
                                  autocomplete="name"/>
                    <x-input-error class="mt-2" :messages="$errors->get('form.name')"/>
                </div>

                <div>
                    <x-input-label for="email" :value="__('メール')"/>
                    <x-text-input wire:model="form.email" id="email" name="email" type="email" class="mt-1 block w-full"
                                  required autocomplete="username"/>
                    <x-input-error class="mt-2" :messages="$errors->get('form.email')"/>
                </div>

                <div>
                    <x-input-label for="content" :value="__('メッセージ')"/>
                    <x-textarea wire:model="form.content" id="content" name="content" type="text" class="mt-1 block w-full"
                                required/>
                    <x-input-error class="mt-2" :messages="$errors->get('form.content')"/>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('送信') }}</x-primary-button>
                </div>
            </form>
        @endif
    </div>

    <hr class="my-10 border border-primary">

    <div class="my-20">
        <h2 class="text-3xl my-6">運営者情報</h2>
        <table class="table-auto w-full border-collapse border-2 border-primary">
            <tr class="border border-indigo-500">
                <th class="bg-primary/50 dark:bg-primary/90">B型事業所</th>
                <td class="p-1">ポップカルチャースタジオ未来図</td>
            </tr>
            <tr class="border border-primary">
                <th class="bg-primary/50 dark:bg-primary/90">法人</th>
                <td class="p-1"><a href="{{ route('company', 1290001091513) }}"
                                   class="text-indigo-500 hover:text-indigo-500 hover:underline">株式会社PayForward</a></td>
            </tr>
            <tr class="border border-primary">
                <th class="bg-primary/50 dark:bg-primary/90">住所</th>
                <td class="p-1">福岡県福岡市博多区博多駅前3-3-12 第6ダイヨシビル2F</td>
            </tr>
        </table>
    </div>
</div>
