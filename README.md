# service-guide

## 技術スタック
- Laravel
- Breeze(livewire-functional)
- Livewire
- AWS
- Vapor

## 開発
```bash
git clone 
cd ./service-guide/

cp .env.example .env
composer install
php artisan key:generate

npm install
npm run build

composer run dev
php artisan migrate
php artisan db:seed
php artisan wam:import
```

## LICENSE
AGPL  
