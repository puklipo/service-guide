<header class="py-3">
    <h1 class="text-3xl my-1 hidden">
        <a href="{{ route('home') }}" class="link link-hover">{{ config('app.name') }}</a>
    </h1>

    <ul class="menu menu-horizontal">
        <li>
            <a href="{{ route('home') }}" aria-label="ホーム" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <span class="icon-[tabler--home] size-5"></span>
                ホーム
            </a>
        </li>
        <li>
            <a href="{{ route('contact') }}" aria-label="問い合わせ"
               class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                <span class="icon-[tabler--mail] size-5"></span>
                問い合わせ
            </a>
        </li>
        <li>
            <a href="{{ route('map') }}" aria-label="サイトマップ"
               class="{{ request()->routeIs('map') ? 'active' : '' }}">
                <span class="icon-[tabler--sitemap] size-5"></span>
                サイトマップ
            </a>
        </li>
    </ul>

    {{--    <nav class="pb-3 flex flex-row gap-4 text-sm text-indigo-500 hover:*:underline">--}}
    {{--        <a href="{{ route('home') }}">ホーム</a>--}}
    {{--        <a href="{{ route('contact') }}">問い合わせ</a>--}}
    {{--        <a href="{{ route('map') }}">サイトマップ</a>--}}
    {{--    </nav>--}}


    <div class="text-sm my-2 py-2 px-2 ring-1 ring-primary">
        <a href="https://www.wam.go.jp/sfkohyoout/" class="link link-primary link-animated" target="_blank">WAM NET</a>のオープンデータを基にした障害福祉サービスの検索サイトです。自治体ごとの事業所の一覧を表示するまでを目的にしているので各事業所の詳細は公式サイトやWAMを検索してください。事業所の情報を追加することも可能です。共同生活援助は専用の<a
            href="https://grouphome.guide/" class="link link-primary link-animated"
            target="_blank">障害者グループホームガイド</a>もあります。
    </div>
</header>
