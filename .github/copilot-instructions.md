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
