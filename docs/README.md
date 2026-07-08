# LoveGem Framework

**Laravel se better! Privacy-first PHP framework with advanced features.**

## Features

### Core
- Service Container (IoC)
- Facades
- Service Providers
- Pipeline
- Config Repository
- Helpers

### HTTP
- Router
- Request/Response
- Middleware
- CSRF Protection
- HTTP Client

### Database
- Eloquent ORM
- Query Builder
- Migrations
- Seeders
- Relationships

### Authentication
- Guard System
- Session Guard
- Password Hashing
- API Tokens

### Views
- Blade Templating
- Layouts & Sections
- Components
- Custom Directives

### Validation
- Validator
- 30+ Rules
- Error Messages

### Cache
- Repository
- Remember
- Tags

### Queue
- Job Queue
- Delay
- Connections

### Mail
- Mailer
- SMTP Transport
- HTML/Text Templates

### Events
- Dispatcher
- Listeners
- Wildcards

### Console
- Artisan CLI
- 20+ Commands

### Exception Handling
- Handler
- HTTP Exceptions

### Privacy Features
- Encryption (libsodium)
- GDPR Compliance
- Data Minimization
- Secure Defaults

### Advanced Features
- Task Scheduler
- HTTP Client
- Rate Limiter
- Broadcasting
- File Storage
- Notifications
- Lazy Collections
- Fluent Arrays
- Stringable Objects
- Health Checks
- Profiler
- Webhooks

## Installation

```bash
composer require lovegem/framework
```

## Quick Start

```php
<?php

use LoveGem\Core\Application;
use LoveGem\Container\Container;

$app = new Application(__DIR__);
$app->register(LoveGem\Http\ServiceProvider::class);

$app->router->get('/hello', function () {
    return 'Hello from LoveGem!';
});

$app->run();
```

## Documentation

- [Installation Guide](guide/installation.md)
- [Quick Start](guide/quickstart.md)
- [Routing](guide/routing.md)
- [Eloquent ORM](guide/eloquent.md)
- [Blade Templating](guide/blade.md)
- [Authentication](guide/authentication.md)
- [Cache](guide/cache.md)

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The LoveGem Framework is open-sourced software licensed under the [MIT License](LICENSE).

---

**Made with ❤️ by the LoveGem Community**
