# Eloquent ORM

## Basic Usage

```php
<?php

namespace App\Models;

use LoveGem\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
}
```

## Retrieving Records

```php
// Get all records
$users = User::all();

// Get first record
$user = User::first();

// Find by ID
$user = User::find(1);

// Get by conditions
$user = User::where('email', 'john@example.com')->first();

// Get or fail
$user = User::findOrFail(1);
```

## Inserting Records

```php
// Create record
$user = User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
]);

// Insert and get ID
$id = User::insertGetId([
    'name' => 'John',
    'email' => 'john@example.com',
]);
```

## Updating Records

```php
// Update record
User::where('id', 1)->update(['name' => 'Jane']);

// Find and update
$user = User::find(1);
$user->update(['name' => 'Jane']);

// Save model
$user->name = 'Jane';
$user->save();
```

## Deleting Records

```php
// Delete record
User::where('id', 1)->delete();

// Find and delete
$user = User::find(1);
$user->delete();

// Delete all records
User::truncate();
```

## Relationships

### Has One

```php
class User extends Model
{
    public function phone()
    {
        return $this->hasOne(Phone::class);
    }
}

// Usage
$phone = User::find(1)->phone;
```

### Has Many

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Usage
$posts = User::find(1)->posts;
```

### Belongs To

```php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = Post::find(1)->user;
```

### Belongs To Many

```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

// Usage
$roles = User::find(1)->roles;
```

## Query Builder

```php
// Select
$users = DB::table('users')
    ->where('active', 1)
    ->orderBy('name', 'desc')
    ->take(10)
    ->get();

// Join
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', 'posts.title')
    ->get();

// Aggregate
$count = DB::table('users')->count();
$sum = DB::table('orders')->sum('total');
```

## Scopes

```php
class User extends Model
{
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeEmails($query, $email)
    {
        return $query->where('email', $email);
    }
}

// Usage
$activeUsers = User::active()->get();
$user = User::emails('john@example.com')->first();
```

## Accessors & Mutators

```php
class User extends Model
{
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }
}

// Usage
$user = User::find(1);
echo $user->name; // Uppercase first letter
$user->name = 'john'; // Stored as lowercase
```

## Collections

```php
$users = User::all();

// Filter
$activeUsers = $users->filter(function ($user) {
    return $user->active;
});

// Map
$names = $users->map(function ($user) {
    return $user->name;
});

// Sort
$sorted = $users->sortBy('name');

// Paginate
$users = User::paginate(15);
```

## Next Steps

- [Database & Migrations](database.md)
- [Relationships](eloquent-relationships.md)
