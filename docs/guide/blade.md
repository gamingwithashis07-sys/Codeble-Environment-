# Blade Templating

## Basic Syntax

```html
{{-- Display variable --}}
<h1>{{ $title }}</h1>

{{-- Display unescaped HTML --}}
{!! $rawHtml !!}

{{-- Blade comments --}}
{{-- This is a comment --}}
```

## Layouts

### Create Layout

```html
<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'My App')</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

### Extend Layout

```html
<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1>Welcome to My App</h1>
@endsection
```

## Sections

```html
@section('content')
    <p>This is the content.</p>
@endsection

@show  <!-- Show section immediately -->

@append  <!-- Append to section -->
```

## Conditional Statements

```html
@if($user)
    <p>Welcome, {{ $user->name }}</p>
@elseif($guest)
    <p>Welcome, guest</p>
@else
    <p>Please login</p>
@endif

@unless($user)
    <p>Please login</p>
@endunless

@isset($user)
    <p>User is set</p>
@endisset

@empty($user)
    <p>User is empty</p>
@endempty
```

## Loops

```html
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@endforeach

@forelse($users as $user)
    <p>{{ $user->name }}</p>
@empty
    <p>No users found</p>
@endforelse

@for($i = 0; $i < 10; $i++)
    <p>{{ $i }}</p>
@endfor

@while($condition)
    <p>Looping...</p>
@endwhile
```

## Forms

```html
<form method="POST" action="/users">
    @csrf

    <input type="text" name="name" value="{{ old('name') }}">
    @error('name')
        <span class="error">{{ $message }}</span>
    @enderror

    <button type="submit">Submit</button>
</form>
```

## Includes

```html
@include('partials.header')

@include('partials.header', ['title' => 'Page Title'])
```

## Components

```html
<!-- resources/views/components/alert.blade.php -->
<div class="alert alert-{{ $type }}">
    {{ $slot }}
</div>

<!-- Usage -->
<x-alert type="success">
    Operation successful!
</x-alert>
```

## Slots

```html
<!-- resources/views/components/card.blade.php -->
<div class="card">
    <div class="card-header">
        {{ $header }}
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
    <div class="card-footer">
        {{ $footer }}
    </div>
</div>

<!-- Usage -->
<x-card>
    <x-slot name="header">Card Title</x-slot>

    Card content goes here.

    <x-slot name="footer">Card Footer</x-slot>
</x-card>
```

## Custom Directives

```php
// In ServiceProvider
 Blade::directive('datetime', function ($expression) {
    return "<?php echo ($expression)->format('Y-m-d H:i:s'); ?>";
});
```

```html
{{-- Usage --}}
@datetime($post->created_at)
```

## Raw PHP

```php
@php
    $name = 'John';
    echo $name;
@endphp

<?php
    $name = 'John';
    echo $name;
?>
```

## Stacks

```html
{{-- Push to stack --}}
@push('scripts')
    <script src="/js/app.js"></script>
@endpush

{{-- Multiple pushes --}}
@push('scripts')
    <script src="/js/custom.js"></script>
@endpush

{{-- Render stack --}}
@stack('scripts')
```

## Next Steps

- [Views](views.md)
- [Forms & Validation](validation.md)
