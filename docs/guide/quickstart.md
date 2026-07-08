# Quick Start

## Create Your First Application

### 1. Create a Route

Edit `routes/web.php`:

```php
<?php

use LoveGem\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello from LoveGem!';
});

Route::get('/hello/{name}', function (string $name) {
    return "Hello, {$name}!";
});
```

### 2. Start the Server

```bash
php artisan serve
```

Visit http://localhost:8000

### 3. Create a Controller

```bash
php artisan make:controller HomeController
```

Edit `app/Http/Controllers/HomeController.php`:

```php
<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function show(string $id)
    {
        return view('show', ['id' => $id]);
    }
}
```

### 4. Define Routes

```php
<?php

use LoveGem\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/user/{id}', [HomeController::class, 'show']);
```

### 5. Create a View

Create `resources/views/home.blade.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>My LoveGem App</title>
</head>
<body>
    <h1>Welcome to LoveGem!</h1>
    <p>This is my first application.</p>
</body>
</html>
```

### 6. Create a Model

```bash
php artisan make:model Post
```

Edit `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use LoveGem\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'content'];
}
```

### 7. Create a Migration

```bash
php artisan make:migration create_posts_table
```

Edit `database/migrations/xxxx_create_posts_table.php`:

```php
<?php

use LoveGem\Database\Migrations\Migration;
use LoveGem\Database\Migrations\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### 8. Run Migration

```bash
php artisan migrate
```

### 9. Use in Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    public function store()
    {
        Post::create([
            'title' => request('title'),
            'content' => request('content'),
        ]);

        return redirect('/posts');
    }
}
```

## Next Steps

- [Routing](routing.md)
- [Controllers](controllers.md)
- [Models](models.md)
- [Views](views.md)
