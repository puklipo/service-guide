# service-guide

## 技術スタック
- Laravel
- Breeze(livewire-functional)
- Livewire
- AWS
- Vapor

## 開発

最近のLaravelに従い開発時のデータベースはSQLite。

```dotenv
# .env
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database # 最初のインポート時はsync
```

```bash
git clone 
cd ./service-guide/

cp .env.example .env
composer install
php artisan key:generate

npm install
npm run build

php artisan migrate
php artisan db:seed

# ローカルサーバーとキューワーカーとViteを同時に起動
composer run dev
# 通常の開発時はこれだけでもいい
php artisan serve

# 必ずキューワーカー起動後にwam:importコマンドを実行
php artisan wam:import

# ただし時間がかかりすぎてタイムアウトする場合はQUEUE_CONNECTION=syncにして個別にインポートしたほうがいい
php artisan wam:import 11
```

## LICENSE
AGPL  
