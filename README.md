# LoveGem Framework

[![Latest Stable Version](https://poser.pugx.org/lovegem/framework/v/stable)](https://packagist.org/packages/lovegem/framework)
[![License](https://poser.pugx.org/lovegem/framework/license)](https://github.com/lovegem-framework/lovegem/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/lovegem/framework)](https://packagist.org/packages/lovegem/framework)
[![Build Status](https://github.com/gamingwithashis07-sys/Codeble-Environment-/actions/workflows/ci.yml/badge.svg)](https://github.com/gamingwithashis07-sys/Codeble-Environment-/actions)
[![Code Style](https://img.shields.io/badge/code%20style-PSR--12-blue)](https://www.php-fig.org/psr/psr-12/)
[![Security](https://img.shields.io/badge/security-first-9cf)](https://lovegem.dev/security)

**Laravel se better! Privacy-first PHP framework with advanced features.**

LoveGem is a modern, privacy-first PHP framework inspired by Laravel. It provides everything you need to build amazing web applications, plus additional features that make it even better than Laravel.

## 📚 Documentation

**📖 [Documentation Website](https://gamingwithashis07-sys.github.io/Codeble-Environment-/)**

---

## 🚀 Quick Start

### Installation

```bash
composer require lovegem/framework
```

### Basic Usage

```php
<?php

declare(strict_types=1);

use LoveGem\Core\Application;
use LoveGem\Container\Container;

// Create application
$app = new Application(__DIR__);

// Register service providers
$app->register(LoveGem\Http\ServiceProvider::class);
$app->register(LoveGem\View\ServiceProvider::class);
$app->register(LoveGem\Database\ServiceProvider::class);

// Use the framework
$app->router->get('/hello', function () {
    return 'Hello from LoveGem!';
});

$app->run();
```

---

## ✨ Features

### Core Framework
- **Service Container** - IoC container with dependency injection
- **Service Providers** - Modular service registration
- **Facades** - Static proxy classes
- **Pipeline** - Middleware pipeline
- **Config Repository** - Configuration management
- **Helpers** - Array and String utilities

### HTTP Layer
- **Router** - RESTful routing with middleware
- **Request/Response** - HTTP abstraction
- **Middleware** - 13+ built-in middleware
- **CSRF Protection** - Automatic CSRF tokens
- **HTTP Client** - cURL wrapper with retries

### Database
- **Eloquent ORM** - Beautiful ActiveRecord implementation
- **Relations** - HasOne, HasMany, BelongsTo, BelongsToMany
- **Migrations** - Database version control
- **Seeders** - Database seeding
- **Query Builder** - Fluent query builder

### Authentication
- **Guard System** - Multiple authentication guards
- **Session Guard** - Cookie-based authentication
- **Password Hashing** - bcrypt/Argon2 support
- **API Tokens** - Sanctum-like token management

### Views
- **Blade Templating** - Powerful template engine
- **Layouts & Sections** - Template inheritance
- **Components** - Reusable UI components
- **Custom Directives** - Extend Blade syntax

### Validation
- **Validator** - 30+ validation rules
- **Error Messages** - Customizable messages
- **Custom Rules** - Extend validation system

### Session
- **Store** - Session management
- **Flash Data** - One-time messages
- **CSRF Token** - Cross-site request forgery protection

### Cache
- **Repository** - Multiple cache drivers
- **Remember** - Cache with callback
- **Tags** - Cache tagging

### Queue
- **Job Queue** - Background processing
- **Delay** - Scheduled jobs
- **Connections** - Multiple queue connections

### Mail
- **Mailer** - Email sending
- **SMTP Transport** - SMTP configuration
- **HTML/Text Templates** - Multi-format emails

### Events
- **Dispatcher** - Event system
- **Listeners** - Event handlers
- **Wildcards** - Wildcard listeners

### Logging
- **Logger** - PSR-3 logging
- **Multiple Channels** - File, daily, stack
- **Log Levels** - All levels supported

### Console
- **Artisan CLI** - Command-line interface
- **20+ Commands** - Built-in commands
- **Custom Commands** - Create your own

### Exception Handling
- **Handler** - Centralized exception handling
- **HTTP Exceptions** - Exception codes
- **Error Pages** - Custom error pages

---

## 🔐 Privacy Features (LoveGem Special)

LoveGem puts privacy first with built-in features:

### Encryption Service
```php
use LoveGem\Support\Facades\Crypto;

// Encrypt data
$encrypted = Crypto::encrypt('sensitive data');

// Decrypt data
$decrypted = Crypto::decrypt($encrypted);
```

### GDPR Compliance
```php
use LoveGem\Privacy\GDPR;

// Export user data
$userData = GDPR::exportData($user);

// Delete user data
GDPR::deleteData($user);

// Anonymize user data
GDPR::anonymizeData($user);
```

### Data Minimization
```php
use LoveGem\Privacy\DataMinimization;

// Only collect necessary data
DataMinimization::collect($data, [
    'name' => true,      // Required
    'email' => true,     // Required
    'phone' => false,    // Optional - don't collect
    'address' => false,  // Optional - don't collect
]);
```

---

## 🚀 Advanced Features (Better than Laravel!)

### Task Scheduler
```php
use LoveGem\Scheduler\Schedule;

$schedule = new Schedule();

// Run every 15 minutes
$schedule->call(function () {
    // Cleanup tasks
})->everyFifteenMinutes();

// Run daily at 9 AM
$schedule->command('emails:send')->dailyAt('09:00');

// Run weekly
$schedule->job(new BackupJob)->weekly();
```

### HTTP Client
```php
use LoveGem\Http\Client;

$client = new Client();

// GET request
$response = $client->get('https://api.example.com/users');

// POST with JSON
$response = $client->timeout(10)
    ->withHeaders(['Authorization' => 'Bearer token'])
    ->post('https://api.example.com/users', [
        'name' => 'John',
        'email' => 'john@example.com',
    ]);

// Handle response
if ($response->successful()) {
    $data = $response->json();
}
```

### Rate Limiter
```php
use LoveGem\RateLimiting\RateLimiter;

$rateLimiter = new RateLimiter($cache);

// Limit requests
$rateLimiter->attempt('api', 60, function () {
    return response()->json(['message' => 'Success']);
}, 60); // 60 attempts per minute
```

### Broadcasting (WebSocket)
```php
use LoveGem\Broadcasting\Broadcaster;

$broadcaster = new Broadcaster();

// Define channel
$broadcaster->channel('chat', function ($user) {
    return true;
});

// Broadcast event
$broadcaster->broadcast(['chat'], 'new.message', [
    'user' => $user->name,
    'message' => $message,
]);
```

### File Storage
```php
use LoveGem\Filesystem\Filesystem;

$filesystem = new Filesystem();

// Store file
$filesystem->put('file.txt', 'content', 'local');

// Get file
$content = $filesystem->get('file.txt');

// Delete file
$filesystem->delete('file.txt');

// Check if exists
if ($filesystem->exists('file.txt')) {
    // File exists
}
```

### Notifications
```php
use LoveGem\Notifications\ChannelManager;

$channelManager = new ChannelManager($app);

// Send notification
$notification = new OrderShipped($order);
$channelManager->send($user, $notification);
```

### API Tokens (Sanctum)
```php
use LoveGem\Api\Sanctum;

// Create API token
$token = Sanctum::createApiToken($user, [
    'abilities' => ['posts:read', 'posts:write'],
]);

// Revoke token
Sanctum::revokeApiToken($user, $token);
```

### Health Checks
```php
use LoveGem\Health\HealthChecker;

$health = new HealthChecker();

// Register checks
$health->check('database', function () {
    return $database->ping();
});

$health->check('cache', function () {
    return $cache->ping();
});

// Get report
$report = $health->report();
// ['status' => 'ok', 'checks' => [...]]
```

### Lazy Collections
```php
use LoveGem\Support\LazyCollection\LazyCollection;

// Memory efficient collection
$users = LazyCollection::from($database->cursor());

$users->filter(fn ($user) => $user->active)
    ->map(fn ($user) => $user->name)
    ->each(fn ($name) => echo $name . PHP_EOL);
```

### Fluent Arrays
```php
use LoveGem\Support\Fluent;

$fluent = new Fluent([
    'name' => 'John',
    'email' => 'john@example.com',
]);

echo $fluent->name; // John
echo $fluent->get('email'); // john@example.com
```

### Stringable Objects
```php
use LoveGem\Support\Str;

$string = Str::of('Hello World');

$result = $string->lower()
    ->append('!')
    ->contains('world')
    ->slug();
```

### Webhooks
```php
use LoveGem\Webhooks\WebhookManager;

$webhooks = new WebhookManager();

// Register webhook
$webhooks->register('order.created', 'https://api.example.com/webhook');

// Dispatch webhook
$webhooks->dispatch('order.created', $order);
```

### Profiler (Telescope)
```php
use LoveGem\Profiler\Profiler;

$profiler = Profiler::getInstance();

// Start profiling
$profiler->start('query');

// Execute query
$results = $database->select('SELECT * FROM users');

// Stop profiling
$profiler->stop('query');

// Get report
$report = $profiler->report();
```

---

## 🎨 Artisan Commands

```bash
# Development
php artisan serve
php artisan tinker

# Database
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh
php artisan db:seed

# Generate
php artisan make:model User
php artisan make:controller UserController
php artisan make:migration create_users_table
php artisan make:seeder UserSeeder
php artisan make:test UserTest
php artisan make:policy UserPolicy
php artisan make:middleware AuthMiddleware
php artisan make:command SendEmailsCommand

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Security
php artisan key:generate

# Scheduler
php artisan schedule:run
php artisan schedule:list

# Queue
php artisan queue:work
php artisan queue:listen
php artisan queue:failed
php artisan queue:retry

# Health
php artisan health:check
php artisan health:report
```

---

## 📁 Directory Structure

```
lovegem-framework/
├── app/
│   ├── Console/
│   │   └── Commands/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Providers/
├── bootstrap/
│   └── helpers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── sessions.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── index.php
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── src/
│   ├── Api/
│   ├── Auth/
│   ├── Broadcasting/
│   ├── Cache/
│   ├── Config/
│   ├── Console/
│   ├── Container/
│   ├── Core/
│   ├── Database/
│   ├── Events/
│   ├── Exceptions/
│   ├── Facades/
│   ├── Filesystem/
│   ├── Hashing/
│   ├── Health/
│   ├── Http/
│   ├── Logging/
│   ├── Mail/
│   ├── Notifications/
│   ├── Profiler/
│   ├── Queue/
│   ├── RateLimiting/
│   ├── Scheduler/
│   ├── Session/
│   ├── Support/
│   ├── Validation/
│   ├── View/
│   └── Webhooks/
├── storage/
│   ├── app/
│   ├── cache/
│   ├── framework/
│   ├── logs/
│   └── sessions/
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── phpunit.xml.dist
├── phpstan.neon
├── .php-cs-fixer.dist.php
├── CHANGELOG.md
├── CODE_OF_CONDUCT.md
├── CONTRIBUTING.md
├── LICENSE
├── README.md
└── SECURITY.md
```

---

## 📦 Installation

### Requirements
- PHP 8.1 or higher
- Extensions: mbstring, json, openssl, sodium, dom, fileinfo, bcmath

### Install via Composer
```bash
composer create-project lovegem/framework my-app
cd my-app
cp .env.example .env
php artisan key:generate
php artisan serve
```

### Manual Installation
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

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run unit tests
php vendor/bin/phpunit tests/Unit

# Run feature tests
php vendor/bin/phpunit tests/Feature

# Run with coverage
php vendor/bin/phpunit --coverage-html coverage

# Run static analysis
composer phpstan

# Check code style
composer cs-check

# Fix code style
composer cs-fix
```

---

## 📚 Documentation

- [Official Documentation](https://lovegem.dev/docs)
- [API Reference](https://lovegem.dev/api)
- [Video Tutorials](https://lovegem.dev/videos)
- [Blog](https://lovegem.dev/blog)

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup
```bash
# Clone the repository
git clone https://github.com/lovegem-framework/lovegem.git

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs-check
```

---

## 🐛 Bug Reports

If you discover a bug, please create an issue on [GitHub](https://github.com/lovegem-framework/lovegem/issues).

---

## 🔒 Security

If you discover a security vulnerability, please see [SECURITY.md](SECURITY.md) for details.

---

## 📜 License

LoveGem Framework is open-sourced software licensed under the [MIT License](LICENSE).

---

## 🙏 Credits

LoveGem Framework is inspired by [Laravel](https://laravel.com) and built with ❤️ by the LoveGem community.

### Special Thanks
- Taylor Otwell - Creator of Laravel
- The PHP Community
- All Contributors

---

## 🌟 Support

If LoveGem Framework helped you, please give us a ⭐ on GitHub!

---

## 📧 Contact

- **Documentation**: [https://gamingwithashis07-sys.github.io/Codeble-Environment-/](https://gamingwithashis07-sys.github.io/Codeble-Environment-/)
- **GitHub**: [https://github.com/gamingwithashis07-sys/Codeble-Environment-](https://github.com/gamingwithashis07-sys/Codeble-Environment-)

---

**Made with ❤️ by the LoveGem Community**
