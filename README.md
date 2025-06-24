# service-guide

## 技術スタック
- Laravel
- Breeze(livewire-functional)
- Livewire
- AWS
- Vapor

## 開発

最近のLaravelに従い開発時のデータベースはSQLite。

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

// ローカルサーバーとキューワーカーとViteを同時に起動
composer run dev

// 必ずキューワーカー起動後に`wam:import`コマンドを実行
php artisan wam:import

// ただし時間がかかりすぎるので個別にインポートしたほうがいい
php artisan wam:import 11
```

## LICENSE
AGPL  
