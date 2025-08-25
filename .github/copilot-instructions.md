# GitHub Copilot Instructions

このファイルは、GitHub Copilotが日本の障害福祉サービス施設検索アプリケーションのコードベースを理解するためのガイドです。

## プロジェクト概要

このプロジェクトは、日本全国の障害福祉サービス施設を検索・閲覧できるディレクトリアプリケーションです。都道府県、エリア、サービスタイプによる高度なフィルタリング機能を提供しています。

**主要技術スタック:**
- Laravel 12 (PHP 8.2+)
- Livewire 3 + Volt（関数型コンポーネント）
- Tailwind CSS 4 + FlyOnUI
- Laravel Breeze (認証)
- AWS Vapor (デプロイ)

## 開発コマンド

### ローカル開発環境のセットアップ
```bash
# 初期セットアップ
composer install
npm install
cp .env.example .env
php artisan key:generate

# データベースセットアップ (デフォルトでSQLite使用)
php artisan migrate
php artisan db:seed

# フロントエンドビルド
npm run build      # 本番用ビルド
npm run dev        # Vite開発サーバー起動

# 開発サーバー起動
composer run dev   # ローカルサーバー + キューワーカー + Vite同時起動
# または個別に:
php artisan serve  # ローカルサーバーのみ
php artisan queue:work # キューワーカー

# データインポート (キューワーカー起動後に実行)
php artisan wam:import                # すべてのサービスをインポート
php artisan wam:import 14             # 特定サービスのみインポート
# 注意: インポートがタイムアウトする場合は.envでQUEUE_CONNECTION=syncを設定
```

### テスト
```bash
# 全テスト実行
php artisan test
# または
vendor/bin/phpunit

# 特定のテストスイート実行
vendor/bin/phpunit tests/Feature
vendor/bin/phpunit tests/Unit
```

### コード品質
```bash
# Laravel Pint (コードスタイルチェック)
vendor/bin/pint --test
# Laravel Pint (コードスタイル修正)
vendor/bin/pint
```

### データ管理
```bash
# 施設データをCSVからインポート
php artisan wam:import                # すべてのサービスをインポート
php artisan wam:import 14             # 特定サービスのみインポート
```

## アーキテクチャ概要

### コアモデルと関連
- **Facility**: 障害福祉サービス施設の主要エンティティ
  - 関連: Prefecture (Pref)、Area、Company、Service
  - ULIDを主キーとして使用
  - 本番環境での作成/更新時にIndexNowに自動送信
  
- **Service**: 33種類の障害福祉サービス (config/service.php)
- **Prefecture (Pref)**: 日本の47都道府県
- **Area**: 都道府県内のサブ地域
- **Company**: 施設を運営する事業者

### Livewireコンポーネント
- **Home**: リアルタイムフィルタリング機能付きのメイン検索/フィルタインターフェース
- **Facilityページ**: 個別施設詳細ページ (Voltコンポーネント)
- **Companyページ**: 事業者プロフィールページ (Voltコンポーネント)

### データインポートシステム
- `resources/csv/`内のCSVファイルに施設データを格納
- `ImportCommand`がLaravelのジョブ/バッチを使用してCSVファイルを処理
- データはWAM（福祉医療機構）システムから取得

### フロントエンドアーキテクチャ
- シンプルなページにはVolt関数型コンポーネント
- 複雑なインタラクションには従来のLivewireクラス
- Tailwind CSS 4（日本語フォントM PLUS 2）
- FlyOnUIコンポーネントライブラリ

### デプロイ
- 本番環境: AWS Vapor (サーバーレスLaravel)
- ステージング/本番用に別々のDockerfile
- キューワーカーは別コンテナで実行

## 記事機能

このプロジェクトには障害者福祉サービス事業所のCSVデータから生成される統計・分析記事機能が含まれています。詳細は`resources/articles/README.md`を参照してください。

主な特徴:
- 半年ごとに更新される6つの固定記事テーマ
- 事業者向け市場分析と戦略情報
- マークダウン形式で記事を管理
- `/articles/{YYYYMM}/{article_slug}`でアクセス可能

## 重要なファイルとディレクトリ

### 設定ファイル
- `config/service.php`: サービスタイプ定義（データインポートに重要）
- `config/facility.php`, `config/pref.php`: ドメイン固有の設定
- `config/spam.php`: バリデーション用スパムメール設定
- `config/patch.php`: 事業者の電話番号パッチ
- `config/user.php`: ユーザーロール設定（管理者ユーザーID）
- `resources/csv/`: 施設インポート用CSVデータファイル

### 主要コンポーネント
- `app/Livewire/Home.php`: 計算プロパティを持つメイン検索インターフェース
- `app/Console/Commands/ImportCommand.php`: データインポートオーケストレーション
- `app/Jobs/ImportJob.php`: 個別CSVファイル処理
- `app/Support/IndexNow.php`: 検索エンジンインデックス送信
- `app/Http/Controllers/Api/FacilityController.php`: 施設検索用APIエンドポイント
- `app/Http/Resources/FacilityResource.php`: API用リソース変換
- `app/Rules/Spammer.php`: スパム検出カスタムバリデーションルール
- `app/Casts/Telephone.php`: 電話番号パッチ用カスタムキャスト

### ビューとアセット
- `resources/views/livewire/`: Livewireコンポーネントテンプレート
- `resources/views/components/json-ld/`: SEO用構造化データ
- `routes/web.php`と`routes/api.php`にルート定義

## 開発ノート

### データモデル
- 施設にはパフォーマンスとセキュリティ向上のためULID主キーを使用
- Eloquentリレーションシップとイーガーローディング（`$with`プロパティ）を多用
- 検索機能はリアクティブフィルタリング用のLivewire計算プロパティを使用
- カスタムキャスト（Telephone）でデータベース変更なしでランタイムデータパッチを実現

### セキュリティと管理
- ロールベースのアクセス制御（管理者ゲート、ユーザーID = 1）
- 施設管理とIndexNow操作用の管理者専用コンポーネント
- 外部API統合と設定可能なパターンによるスパム検出
- 環境ベースの機能切り替え（広告、IndexNowなど）

### SEOとパフォーマンス
- 施設ページ用の構造化データ（JSON-LD）
- 検索インデックス向上のためのサイトマップ生成
- リアルタイム検索エンジン更新のためのIndexNow統合
- N+1クエリ防止のためのイーガーローディング設定

### 国際化
- `lang/`ディレクトリに日本語/英語のバイリンガルサポート
- 主要言語は日本語、一部英語サポートあり

### キューシステム
- キューバックエンドにRedisを使用
- インポート操作はパフォーマンス向上のためキューに入れられる
- キューワーカーは別Dockerコンテナで実行

## テスト戦略

### テスト構造
- **機能テスト**: APIエンドポイント、Livewireコンポーネント、認証フロー、管理者制限
- **ユニットテスト**: 個別モデル、キャスト、バリデーションルール、ユーティリティクラス
- 高速化のため`RefreshDatabase`トレイトを使用したSQLiteインメモリデータベース
- `protected $seed = true`によるデータベースシーディングを伴う包括的なテストカバレッジ

### 主要テスト領域

#### APIテスト (`tests/Feature/Api/`)
- **FacilityController**: フィルタリング、ページネーション、JSON構造検証を含む包括的なAPIエンドポイントテスト
- サービス/都道府県/エリアフィルタリング、複合フィルタ、部分一致のテスト
- APIリソース構造とページネーションメタデータの検証

#### Livewireコンポーネントテスト (`tests/Feature/Livewire/`)
- **Homeコンポーネント**: 計算プロパティ、リアルタイムフィルタリング、URLパラメータバインディングのテスト
- 検索機能、ページネーション制限、フィルタ組み合わせの検証
- コンポーネントレンダリングとデータフローのテスト

#### モデルテスト (`tests/Unit/Models/`)
- **Facility**: ULID使用、リレーションシップ、fillable属性、イーガーローディング、IndexNow統合
- **Area、Company、Service、Pref**: リレーションシップテストとモデル動作
- ファクトリー作成とモデル検証のテスト

#### カスタムコンポーネントテスト
- **キャスト** (`tests/Unit/Casts/TelephoneTest.php`): 設定パッチ付きカスタム電話キャストのテスト
- **ルール** (`tests/Unit/Rules/SpammerTest.php`): API統合とワイルドカードパターンを伴うスパム検証
- **管理者制限** (`tests/Feature/AdminRestrictionsTest.php`): ロールベースのアクセス制御と管理者専用機能

### テストパターン
- Laravelのファサード（`getJson`、`assertJsonStructure`など）を使用したHTTPテスト
- コンポーネントインタラクション用の`Livewire::test()`
- 信頼性の高いテスト用の`Http::fake()`による外部API模擬
- 検証用の`Log::spy()`によるログテスト
- 管理者と非管理者ユーザーの認可テスト用のゲートテスト

### データファクトリー
- すべてのモデルに現実的な日本語データを持つ対応するファクトリーがある
- リレーションシップテスト用に関連モデルを自動的に作成するファクトリー
- 一貫したテスト用に都道府県、サービス、地域を含むシードデータ

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.8
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/pint (PINT) - v1
- tailwindcss (TAILWINDCSS) - v4


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()`) for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== volt/core rules ===

## Livewire Volt

- This project uses Livewire Volt for interactivity within its pages. New pages requiring interactivity must also use Livewire Volt. There is documentation available for it.
- Make new Volt components using `php artisan make:volt [name] [--test] [--pest]`
- Volt is a **class-based** and **functional** API for Livewire that supports single-file components, allowing a component's PHP logic and Blade templates to co-exist in the same file
- Livewire Volt allows PHP logic and Blade templates in one file. Components use the `@livewire("volt-anonymous-fragment-eyJuYW1lIjoidm9sdC1hbm9ueW1vdXMtZnJhZ21lbnQtYmQ5YWJiNTE3YWMyMTgwOTA1ZmUxMzAxODk0MGJiZmIiLCJwYXRoIjoic3RvcmFnZVwvZnJhbWV3b3JrXC92aWV3c1wvMTUxYWRjZWRjMzBhMzllOWIxNzQ0ZDRiMWRjY2FjYWIuYmxhZGUucGhwIn0=", Livewire\Volt\Precompilers\ExtractFragments::componentArguments([...get_defined_vars(), ...array (
)]))
</code-snippet>


### Volt Class Based Component Example
To get started, define an anonymous class that extends Livewire\Volt\Component. Within the class, you may utilize all of the features of Livewire using traditional Livewire syntax:


<code-snippet name="Volt Class-based Volt Component Example" lang="php">
use Livewire\Volt\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
} ?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
</code-snippet>


### Testing Volt & Volt Components
- Use the existing directory for tests if it already exists. Otherwise, fallback to `tests/Feature/Volt`.

<code-snippet name="Livewire Test Example" lang="php">
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
</code-snippet>


<code-snippet name="Volt Component Test Using Pest" lang="php">
declare(strict_types=1);

use App\Models\{User, Product};
use Livewire\Volt\Volt;

test('product form creates product', function () {
    $user = User::factory()->create();

    Volt::test('pages.products.create')
        ->actingAs($user)
        ->set('form.name', 'Test Product')
        ->set('form.description', 'Test Description')
        ->set('form.price', 99.99)
        ->call('create')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});
</code-snippet>


### Common Patterns


<code-snippet name="CRUD With Volt" lang="php">
<?php

use App\Models\Product;
use function Livewire\Volt\{state, computed};

state(['editing' => null, 'search' => '']);

$products = computed(fn() => Product::when($this->search,
    fn($q) => $q->where('name', 'like', "%{$this->search}%")
)->get());

$edit = fn(Product $product) => $this->editing = $product->id;
$delete = fn(Product $product) => $product->delete();

?>

<!-- HTML / UI Here -->
</code-snippet>



<code-snippet name="Real-Time Search With Volt" lang="php">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />
</code-snippet>



<code-snippet name="Loading States With Volt" lang="php">
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff"
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>