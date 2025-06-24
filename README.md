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

php artisan migrate
php artisan db:seed

// Start local development server with queue worker and vite server
composer run dev

// Be sure to start the queue worker with `composer run dev` before running the `wam:import` command
php artisan wam:import

// If it takes too long, import individually
php artisan wam:import 11
```

## LICENSE
AGPL  
