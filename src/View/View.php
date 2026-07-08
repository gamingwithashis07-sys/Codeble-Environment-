<?php

declare(strict_types=1);

namespace LoveGem\View;

class View
{
    protected Factory $factory;

    protected string $compiled;

    protected array $data;

    public function __construct(Factory $factory, string $compiled, array $data)
    {
        $this->factory = $factory;
        $this->compiled = $compiled;
        $this->data = $data;
    }

    public function render(): string
    {
        ob_start();

        extract($this->data, EXTR_SKIP);

        eval('?>' . $this->compiled);

        return ob_get_clean();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }
}
