# Authentication

## Basic Setup

```php
// routes/web.php
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);
```

## Login Controller

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LoveGem\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        $credentials = request()->only('email', 'password');

        if (Auth::attempt($credentials)) {
            request()->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }
}
```

## Protecting Routes

```php
// Middleware
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Multiple middleware
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'admin']);
```

## Accessing User

```php
// Get authenticated user
$user = Auth::user();
$user = Auth::user();

// Get user ID
$id = Auth::id();

// Check authentication
if (Auth::check()) {
    // User is authenticated
}

// Guest check
if (Auth::guest()) {
    // User is guest
}
```

## Remember Me

```php
// Login with remember
Auth::attempt($credentials, true);
```

## Registration

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use LoveGem\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register()
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make(request('password')),
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
```

## Password Reset

```php
// routes/web.php
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
```

## Custom Guards

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],
],
```

```php
// Use specific guard
Auth::guard('admin')->attempt($credentials);
```

## API Tokens (Sanctum)

```php
use LoveGem\Api\Sanctum;

// Create token
$token = Sanctum::createApiToken($user, [
    'abilities' => ['posts:read', 'posts:write'],
]);

// Revoke token
Sanctum::revokeApiToken($user, $token);
```

## Next Steps

- [Authorization](authorization.md)
- [Middleware](middleware.md)
