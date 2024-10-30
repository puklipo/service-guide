<header class="py-3">
    <h1 class="text-3xl my-1 hidden">
        <a href="{{ route('home') }}" class="link link-hover">{{ config('app.name') }}</a>
    </h1>

    <ul class="menu menu-horizontal">
        <li>
            <a href="{{ route('home') }}" aria-label="ホーム">
                <span class="icon-[tabler--home] size-5"></span>
                ホーム
            </a>
        </li>
        @if(Route::has('contact'))
            <li>
                <a href="{{ route('contact') }}" aria-label="問い合わせ"
                   class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                    <span class="icon-[tabler--mail] size-5"></span>
                    問い合わせ
                </a>
            </li>
        @endif
        <li>
            <a href="{{ route('map') }}" aria-label="サイトマップ"
               class="{{ request()->routeIs('map') ? 'active' : '' }}">
                <span class="icon-[tabler--sitemap] size-5"></span>
                サイトマップ
            </a>
        </li>
    </ul>

    <div class="text-sm my-2 p-3 border-base-300 bg-base-200 border rounded-lg">
        <a href="https://www.wam.go.jp/sfkohyoout/" class="link link-primary link-animated" target="_blank">WAM NET</a>のオープンデータを基にした障害福祉サービスの検索サイトです。自治体ごとの事業所の一覧を表示するまでを目的にしているので各事業所の詳細は公式サイトやWAMを検索してください。事業所の情報を追加することも可能です。共同生活援助は専用の<a
            href="https://grouphome.guide/" class="link link-primary link-animated"
            target="_blank">障害者グループホームガイド</a>もあります。
    </div>
</header>
