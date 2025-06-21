# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Japanese disability service facility directory application built with Laravel 12, using Livewire/Volt for frontend interactivity. The application helps users find and browse disability service facilities across Japan, with advanced filtering by prefecture, area, and service type.

**Key Technologies:**
- Laravel 12 with PHP 8.2+
- Livewire 3 + Volt (functional components)
- Tailwind CSS 4 + FlyOnUI
- Laravel Breeze for authentication
- Laravel Sail for local development
- AWS Vapor for deployment

## Development Commands

### Local Development Setup
```bash
# Initial setup
composer install
npm install
cp .env.example .env
php artisan key:generate

# Start development environment
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan wam:import

# Frontend development
npm run dev        # Start Vite dev server
npm run build      # Build for production
```

### Testing
```bash
# Run all tests
./vendor/bin/sail artisan test
# Or with vendor/bin/phpunit
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit tests/Feature
vendor/bin/phpunit tests/Unit
```

### Code Quality
```bash
# Laravel Pint (code style)
./vendor/bin/pint

# Generate IDE helpers
php artisan ide-helper:models -M
```

### Data Management
```bash
# Import facility data from CSV files
php artisan wam:import                # Import all services
php artisan wam:import 11             # Import specific service (居宅介護)

# Generate sitemap
php artisan sitemap:generate
```

## Architecture Overview

### Core Models & Relationships
- **Facility**: Main entity representing disability service facilities
  - Belongs to: Prefecture (Pref), Area, Company, Service
  - Uses ULIDs for primary keys
  - Auto-submits to IndexNow on create/update in production
  
- **Service**: 33 types of disability services (config/service.php)
- **Prefecture (Pref)**: 47 Japanese prefectures 
- **Area**: Sub-regions within prefectures
- **Company**: Organizations operating facilities

### Livewire Components
- **Home**: Main search/filter interface with real-time filtering
- **Facility pages**: Individual facility detail pages (Volt components)
- **Company pages**: Company profile pages (Volt components)

### Data Import System
- CSV files in `resources/csv/` contain facility data
- `ImportCommand` processes CSV files using Laravel jobs/batches
- Data sourced from WAM (Welfare and Medical Service Agency) system

### Frontend Architecture
- Volt functional components for simple pages
- Traditional Livewire classes for complex interactions
- Tailwind CSS 4 with Japanese font (M PLUS 2)
- FlyOnUI component library

### Deployment
- Production: AWS Vapor (serverless Laravel)
- Uses separate staging/production Dockerfiles
- Queue workers run in separate containers

## Important Files & Directories

### Configuration
- `config/service.php`: Service type definitions (important for data import)
- `config/facility.php`, `config/pref.php`: Domain-specific configurations
- `resources/csv/`: CSV data files for facility import

### Key Components
- `app/Livewire/Home.php`: Main search interface with computed properties
- `app/Console/Commands/ImportCommand.php`: Data import orchestration
- `app/Jobs/ImportJob.php`: Individual CSV file processing
- `app/Support/IndexNow.php`: Search engine index submission

### Views & Assets
- `resources/views/livewire/`: Livewire component templates
- `resources/views/components/json-ld/`: Structured data for SEO
- Routes defined in `routes/web.php` and `routes/api.php`

## Development Notes

### Data Model
- Facilities use ULID primary keys for better performance and security
- Heavy use of Eloquent relationships with eager loading (`$with` property)
- Search functionality uses computed properties in Livewire for reactive filtering

### SEO & Performance
- Structured data (JSON-LD) for facility pages
- Sitemap generation for better search indexing
- IndexNow integration for real-time search engine updates
- Eager loading configured to prevent N+1 queries

### Internationalization
- Bilingual support (Japanese/English) in `lang/` directory
- Primary language is Japanese with some English support

### Queue System
- Uses Redis for queue backend
- Import operations are queued for better performance
- Queue worker runs in separate Docker container

## Testing Strategy

- Feature tests cover authentication flows and key user journeys
- Unit tests for individual components
- Tests use SQLite in-memory database for speed
- Test configuration in `phpunit.xml`
