# Cache

## Basic Usage

```php
use LoveGem\Support\Facades\Cache;

// Store value in cache
Cache::put('key', 'value', 60); // 60 minutes

// Get value from cache
$value = Cache::get('key', 'default');

// Check if exists
if (Cache::has('key')) {
    // Value exists
}

// Delete value
Cache::forget('key');

// Flush all cache
Cache::flush();
```

## Cache Tags

```php
// Store with tags
Cache::tags(['posts', 'users'])->put('key', 'value', 60);

// Get with tags
$value = Cache::tags(['posts'])->get('key');

// Flush specific tags
Cache::tags(['posts'])->flush();
```

## Remember

```php
// Get or compute and store
$value = Cache::remember('key', 60, function () {
    return expensiveOperation();
});

// Get or store forever
$value = Cache::rememberForever('key', function () {
    return expensiveOperation();
});
```

## Cache Drivers

```php
// File driver
Cache::store('file')->get('key');

// Database driver
Cache::store('database')->get('key');

// Redis driver
Cache::store('redis')->get('key');

// Array driver (testing)
Cache::store('array')->get('key');
```

## Cache Operations

```php
// Increment
Cache::increment('key');
Cache::increment('key', 5);

// Decrement
Cache::decrement('key');
Cache::decrement('key', 5);

// Store forever
Cache::forever('key', 'value');

// Pull (get and delete)
$value = Cache::pull('key');
```

## Cache Helpers

```php
// Get many
$values = Cache::getMany(['key1', 'key2', 'key3']);

// Put many
Cache::putMany([
    'key1' => 'value1',
    'key2' => 'value2',
], 60);

// Add (store if not exists)
Cache::add('key', 'value', 60);

// Add many
Cache::addMany([
    'key1' => 'value1',
    'key2' => 'value2',
], 60);
```

## Cache Configuration

```php
// config/cache.php
return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
        ],
        'database' => [
            'driver' => 'database',
            'connection' => 'cache',
            'table' => 'cache',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'lovegem'),
];
```

## Cache Events

```php
// Listen to cache events
Event::listen(CacheHit::class, function ($event) {
    // Cache hit
});

Event::listen(CacheMissed::class, function ($event) {
    // Cache miss
});
```

## Next Steps

- [Queue](queue.md)
- [Session](sessions.md)
