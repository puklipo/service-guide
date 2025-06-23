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
php artisan key:generate
composer install
npm install
npm run build

sail up -d
sail art migrate
sail art db:seed
sail art wam:import
```

## LICENSE
AGPL  
