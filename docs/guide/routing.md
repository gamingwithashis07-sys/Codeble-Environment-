# Routing

## Basic Routes

```php
<?php

use LoveGem\Support\Facades\Route;

// GET route
Route::get('/users', [UserController::class, 'index']);

// POST route
Route::post('/users', [UserController::class, 'store']);

// PUT route
Route::put('/users/{id}', [UserController::class, 'update']);

// DELETE route
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Any HTTP method
Route::match(['get', 'post'], '/users', [UserController::class, 'index']);

// All HTTP methods
Route::any('/users', [UserController::class, 'index']);
```

## Route Parameters

```php
// Required parameters
Route::get('/users/{id}', [UserController::class, 'show']);

// Optional parameters
Route::get('/users/{id?}', [UserController::class, 'show']);

// Multiple parameters
Route::get('/posts/{post}/comments/{comment}', [CommentController::class, 'show']);
```

## Route Groups

```php
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
});
```

## Named Routes

```php
Route::get('/users', [UserController::class, 'index'])->name('users.index');

// Generate URL
$url = route('users.index');

// Redirect
return redirect()->route('users.index');
```

## Route Middleware

```php
Route::get('/profile', [ProfileController::class, 'show'])
    ->middleware('auth');

Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'admin']);
```

## Route Parameters Constraints

```php
Route::get('/users/{id}', [UserController::class, 'show'])
    ->where('id', '[0-9]+');

Route::get('/posts/{slug}', [PostController::class, 'show'])
    ->where('slug', '[a-z-]+');
```

## API Routes

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::get('/users', [Api\UserController::class, 'index']);
    Route::post('/users', [Api\UserController::class, 'store']);
});
```

## Resource Routes

```php
Route::resource('posts', PostController::class);

// Creates:
// GET    /posts          -> index
// GET    /posts/create   -> create
// POST   /posts          -> store
// GET    /posts/{post}   -> show
// GET    /posts/{post}/edit -> edit
// PUT    /posts/{post}   -> update
// DELETE /posts/{post}   -> destroy
```

## Route Model Binding

```php
Route::get('/posts/{post}', [PostController::class, 'show']);

// Controller
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}
```

## Fallback Routes

```php
Route::fallback(function () {
    return response()->json(['message' => 'Not Found'], 404);
});
```

## Next Steps

- [Controllers](controllers.md)
- [Middleware](middleware.md)
