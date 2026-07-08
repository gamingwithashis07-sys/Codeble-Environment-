<?php

declare(strict_types=1);

namespace LoveGem\Validation;

use Closure;

class Factory
{
    protected array $rules = [];

    protected array $messages = [];

    protected array $customMessages = [];

    protected array $extensions = [];

    public function make(array $data, array $rules, array $messages = [], array $customAttributes = []): Validator
    {
        $validator = new Validator($data, $rules, $this->customMessages);

        $validator->setCustomAttributes($customAttributes);

        return $validator;
    }

    public function extend(string $rule, Closure $extension): void
    {
        $this->extensions[$rule] = $extension;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
