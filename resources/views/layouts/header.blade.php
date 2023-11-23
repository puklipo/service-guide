<header class="py-3">
    <nav class="pb-3 flex flex-row gap-4 text-xs text-indigo-500">
        <div><a href="{{ route('contact') }}" class="hover:underline" wire:navigate>問い合わせ</a></div>
        <div><a href="{{ route('map') }}" class="hover:underline" wire:navigate>サイトマップ</a></div>
    </nav>

    <h1 class="text-3xl my-1">
        <a href="{{ route('home') }}" class="hover:text-indigo-500 hover:underline" wire:navigate>{{ config('app.name') }}</a>
    </h1>

    <div class="text-sm my-2 py-2 px-2 ring-1 ring-indigo-500">
        <a href="https://www.wam.go.jp/sfkohyoout/" class="text-indigo-500 hover:underline" target="_blank">WAM NET</a>のオープンデータを基にした障害福祉サービスの検索サイトです。自治体ごとの事業所の一覧を表示するまでを目的にしているので各事業所の詳細は公式サイトやWAMを検索してください。共同生活援助は専用サイトの<a href="https://grouphome.guide/" class="text-indigo-500 hover:underline" target="_blank">障害者グループホームガイド</a>もあります。
    </div>
</header>
