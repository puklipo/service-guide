<?php

use App\Notifications\ContactNotification;
use Illuminate\Support\Facades\Notification;
use function Livewire\Volt\{state};
use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('layouts.app');

state(['name', 'email', 'content']);

title('問い合わせ '.config('app.name'));

$sendmail = function () {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        'content' => ['required', 'string', 'max:4000'],
    ]);

    Notification::route('mail', config('mail.contact.to'))
        ->notify(new ContactNotification(name: $validated['name'], email: $validated['email'], content: $validated['content']));

    session()->flash('mail_success');
}
?>
<div class="text-xl mx-1 sm:mx-10">
    @include('layouts.header')

    <h2 class="text-4xl my-6">問い合わせ</h2>

    <div class="text-md my-2 py-2 px-2">
        閉鎖済み事業所の削除依頼などはこちらから連絡してください。削除依頼時は当サイト内の事業所のURLと事業所番号を記入してください。情報の修正はWAM
        NET側を修正してください。
    </div>

    <div class="px-2">
        @if(session()->has('mail_success'))
            <div class="font-bold">送信しました。返信をお待ちください。</div>
        @else
            <form wire:submit="sendmail" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="name" :value="__('名前')"/>
                    <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required
                                  autofocus autocomplete="name"/>
                    <x-input-error class="mt-2" :messages="$errors->get('name')"/>
                </div>

                <div>
                    <x-input-label for="email" :value="__('メール')"/>
                    <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full"
                                  required
                                  autocomplete="username"/>
                    <x-input-error class="mt-2" :messages="$errors->get('email')"/>
                </div>

                <div>
                    <x-input-label for="content" :value="__('メッセージ')"/>
                    <x-textarea wire:model="content" id="content" name="content" type="text" class="mt-1 block w-full"
                                required/>
                    <x-input-error class="mt-2" :messages="$errors->get('content')"/>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('送信') }}</x-primary-button>
                </div>
            </form>
        @endif
    </div>

    <hr class="my-10 border border-indigo-500">

    <div class="my-20">
        <h2 class="text-3xl my-6">運営者情報</h2>
        <table class="table-auto w-full border-collapse border-2 border-indigo-500">
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">B型事業所</th>
                <td class="p-1">ポップカルチャースタジオ未来図</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">法人</th>
                <td class="p-1">株式会社PayForward</td>
            </tr>
            <tr class="border border-indigo-500">
                <th class="bg-indigo-300">住所</th>
                <td class="p-1">福岡県福岡市博多区博多駅前3-3-12　第6ダイヨシビル2F</td>
            </tr>
        </table>
    </div>
</div>
