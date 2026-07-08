# Installation

## Requirements

- PHP 8.1 or higher
- Extensions:
  - mbstring
  - json
  - openssl
  - sodium
  - dom
  - fileinfo
  - bcmath

## Install via Composer

```bash
composer create-project lovegem/framework my-app
```

## Manual Installation

```bash
# Clone repository
git clone https://github.com/lovegem-framework/lovegem.git
cd lovegem

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations (if using database)
php artisan migrate

# Start development server
php artisan serve
```

## Configuration

### Environment Variables

Copy `.env.example` to `.env` and configure:

```env
APP_NAME="My App"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Generate Application Key

```bash
php artisan key:generate
```

### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=secret
```

### Run Migrations

```bash
php artisan migrate
```

## Verify Installation

```bash
php artisan --version
# LoveGem Framework v1.0.0

php artisan serve
# Laravel development server started: http://127.0.0.1:8000
```

## Next Steps

- [Quick Start](quickstart.md)
- [Configuration](configuration.md)
