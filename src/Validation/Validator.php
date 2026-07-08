<?php

declare(strict_types=1);

namespace LoveGem\Validation;

use Closure;

class Validator
{
    protected array $data;

    protected array $rules;

    protected array $messages = [];

    protected array $customMessages = [];

    protected array $customAttributes = [];

    protected array $errors = [];

    protected array $validated = [];

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ((array) $rules as $rule) {
                $this->validateField($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    protected function validateField(string $field, mixed $value, string|array $rule): void
    {
        if (is_array($rule)) {
            $method = $rule[0];
            $parameters = array_slice($rule, 1);
        } else {
            $parameters = [];
            $method = $rule;
        }

        if (method_exists($this, $method)) {
            $result = $this->{$method}($field, $value, ...$parameters);

            if ($result === false) {
                $this->errors[$field][] = $this->getMessage($field, $method, $parameters);
            }
        }
    }

    protected function getMessage(string $field, string $rule, array $parameters): string
    {
        $key = "{$field}.{$rule}";

        if (isset($this->customMessages[$key])) {
            $message = $this->customMessages[$key];
        } else {
            $message = $this->getDefaultMessage($rule);
        }

        $attribute = $this->customAttributes[$field] ?? $field;

        return str_replace([':attribute', ':input'], [$attribute, $this->data[$field] ?? ''], $message);
    }

    protected function getDefaultMessage(string $rule): string
    {
        $messages = [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'string' => 'The :attribute must be a string.',
            'integer' => 'The :attribute must be an integer.',
            'numeric' => 'The :attribute must be a number.',
            'boolean' => 'The :attribute field must be true or false.',
            'max' => 'The :attribute must not be greater than :max characters.',
            'min' => 'The :attribute must be at least :min characters.',
            'size' => 'The :attribute must be :size characters.',
            'in' => 'The selected :attribute is invalid.',
            'not_in' => 'The selected :attribute is invalid.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'date' => 'The :attribute must be a valid date.',
            'url' => 'The :attribute must be a valid URL.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
            'before' => 'The :attribute must be a date before :date.',
            'after' => 'The :attribute must be a date after :date.',
            'array' => 'The :attribute must be an array.',
            'url' => 'The :attribute format is invalid.',
            'ip' => 'The :attribute must be a valid IP address.',
            'json' => 'The :attribute must be a valid JSON string.',
            'alpha' => 'The :attribute must only contain letters.',
            'alpha_num' => 'The :attribute must only contain letters and numbers.',
            'alpha_dash' => 'The :attribute must only contain letters, numbers, and dashes.',
            'date_format' => 'The :attribute does not match the format :format.',
            'different' => 'The :attribute and :other must be different.',
            'digits' => 'The :attribute must be :digits digits.',
            'digits_between' => 'The :attribute must be between :min and :max digits.',
            'dimensions' => 'The :attribute has invalid image dimensions.',
            'image' => 'The :attribute must be an image.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'mimetypes' => 'The :attribute must be a file of type: :values.',
            'nullable' => 'The :attribute field must be present.',
            'present' => 'The :attribute field must be present.',
            'required_if' => 'The :attribute field is required when :other is :value.',
            'required_unless' => 'The :attribute field is required unless :other is in :values.',
            'required_with' => 'The :attribute field is required when :values is present.',
            'required_with_all' => 'The :attribute field is required when :values is present.',
            'required_without' => 'The :attribute field is required when :values is not present.',
            'required_without_all' => 'The :attribute field is required when none of :values are present.',
            'same' => 'The :attribute and :other must match.',
            'string' => 'The :attribute must be a string.',
            'timezone' => 'The :attribute must be a valid timezone.',
            'uuid' => 'The :attribute must be a valid UUID.',
        ];

        return $messages[$rule] ?? "The :attribute field is invalid.";
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstErrors(): array
    {
        $errors = [];

        foreach ($this->errors as $field => $messages) {
            $errors[$field] = $messages[0];
        }

        return $errors;
    }

    public function failed(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        $this->validate();

        $validated = [];

        foreach ($this->rules as $field => $rules) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    public function setCustomAttributes(array $customAttributes): void
    {
        $this->customAttributes = $customAttributes;
    }

    public function passes(): bool
    {
        return $this->validate();
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    // Validation Rules

    protected function required(string $field, mixed $value): bool
    {
        if (is_null($value) || $value === '') {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    protected function email(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function string(string $field, mixed $value): bool
    {
        return is_string($value);
    }

    protected function integer(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function numeric(string $field, mixed $value): bool
    {
        return is_numeric($value);
    }

    protected function boolean(string $field, mixed $value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1']);
    }

    protected function max(string $field, mixed $value, int $max): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        return $value <= $max;
    }

    protected function min(string $field, mixed $value, int $min): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        return $value >= $min;
    }

    protected function size(string $field, mixed $value, int $size): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) === $size;
        }

        return $value == $size;
    }

    protected function in(string $field, mixed $value, string ...$values): bool
    {
        return in_array($value, $values);
    }

    protected function not_in(string $field, mixed $value, string ...$values): bool
    {
        return !in_array($value, $values);
    }

    protected function confirmed(string $field, mixed $value): bool
    {
        $confirmation = $this->data["{$field}_confirmation"] ?? null;
        return $value === $confirmation;
    }

    protected function date(string $field, mixed $value): bool
    {
        return strtotime($value) !== false;
    }

    protected function url(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function ip(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    protected function json(string $field, mixed $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function alpha(string $field, mixed $value): bool
    {
        return ctype_alpha($value);
    }

    protected function alpha_num(string $field, mixed $value): bool
    {
        return ctype_alnum($value);
    }

    protected function alpha_dash(string $field, mixed $value): bool
    {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $value);
    }

    protected function array(string $field, mixed $value): bool
    {
        return is_array($value);
    }

    protected function nullable(string $field, mixed $value): bool
    {
        return true;
    }

    protected function present(string $field, mixed $value): bool
    {
        return array_key_exists($field, $this->data);
    }

    protected function different(string $field, mixed $value, string $other): bool
    {
        return $value !== ($this->data[$other] ?? null);
    }

    protected function same(string $field, mixed $value, string $other): bool
    {
        return $value === ($this->data[$other] ?? null);
    }

    protected function before(string $field, mixed $value, string $date): bool
    {
        return strtotime($value) < strtotime($date);
    }

    protected function after(string $field, mixed $value, string $date): bool
    {
        return strtotime($value) > strtotime($date);
    }

    protected function date_format(string $field, mixed $value, string $format): bool
    {
        $d = \DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }
}
