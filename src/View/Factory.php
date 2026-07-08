<?php

declare(strict_types=1);

namespace LoveGem\View;

use LoveGem\View\Compilers\BladeCompiler;

class Factory
{
    protected BladeCompiler $compiler;

    protected array $sections = [];

    protected array $sectionStack = [];

    protected array $layouts = [];

    protected array $shared = [];

    protected string $currentLayout;

    public function __construct(BladeCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    public function make(string $view, array $data = [], array $mergeData = []): View
    {
        $path = $this->findViewPath($view);

        $data = array_merge($this->shared, $mergeData, $data);

        $compiled = $this->compileView($path, $data);

        return new View($this, $compiled, $data);
    }

    protected function findViewPath(string $view): string
    {
        $paths = [
            resource_path("views/{$view}.blade.php"),
            resource_path("views/{$view}.php"),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException("View [{$view}] not found.");
    }

    protected function compileView(string $path, array $data): string
    {
        $template = file_get_contents($path);

        $compiled = $this->compiler->compile($template);

        return $compiled;
    }

    public function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } else {
            $this->shared[$key] = $value;
        }
    }

    public function startSection(string $name, string $content = ''): void
    {
        $this->sections[$name] = $content;
        $this->sectionStack[] = $name;
    }

    public function appendSection(): void
    {
        $name = end($this->sectionStack);
        $this->sections[$name] = '';
    }

    public function stopSection(): string
    {
        $name = array_pop($this->sectionStack);

        return $this->sections[$name] ?? '';
    }

    public function yieldSection(): string
    {
        $name = array_pop($this->sectionStack);

        return $this->sections[$name] ?? '';
    }

    public function yield(string $section, string $default = ''): string
    {
        return $this->sections[$section] ?? $default;
    }

    public function extend(string $layout): void
    {
        $this->currentLayout = $layout;
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function getSectionStack(): array
    {
        return $this->sectionStack;
    }

    public function compiler(): BladeCompiler
    {
        return $this->compiler;
    }
}
