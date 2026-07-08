<?php

declare(strict_types=1);

namespace LoveGem\Database\Eloquent;

use LoveGem\Support\Str;
use Carbon\Carbon;

abstract class Model
{
    protected string $connection = '';

    protected string $table = '';

    protected string $primaryKey = 'id';

    protected bool $incrementing = true;

    protected string $keyType = 'int';

    protected bool $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected ?string $dateFormat = null;

    protected array $appends = [];

    protected array $hidden = [];

    protected array $fillable = [];

    protected array $guarded = ['*'];

    protected array $visible = [];

    protected array $casts = [];

    protected bool $exists = false;

    protected bool $wasRecentlyCreated = false;

    protected array $original = [];

    protected array $attributes = [];

    protected array $relations = [];

    protected static array $booted = [];

    protected static array $globalScopes = [];

    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->syncOriginal();
        $this->fill($attributes);
    }

    protected function bootIfNotBooted(): void
    {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;
            static::boot();
        }
    }

    protected static function boot(): void
    {
        //
    }

    public static function all(array $columns = ['*']): Collection
    {
        return static::query()->get($columns);
    }

    public static function query(): Builder
    {
        return static::newModelInstance()->newQuery();
    }

    public static function find(mixed $id, array $columns = ['*']): ?static
    {
        return static::query()->find($id, $columns);
    }

    public static function findOrFail(mixed $id, array $columns = ['*']): static
    {
        $result = static::find($id, $columns);

        if (is_null($result)) {
            throw new \RuntimeException("No query result for model [" . static::class . "]");
        }

        return $result;
    }

    public static function create(array $attributes = []): static
    {
        $model = static::newModelInstance();
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    public static function firstOrCreate(array $attributes, array $values = []): static
    {
        $instance = static::where($attributes)->first();

        if (!$instance) {
            $instance = static::create(array_merge($attributes, $values));
        }

        return $instance;
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    public function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) {
            return false;
        }

        return $this->guarded === [] || in_array($key, $this->fillable);
    }

    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        return $this->attributes[$key] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    public function isDirty(array|string|null $attributes = null): bool
    {
        $dirty = $this->getDirty();

        if (is_null($attributes)) {
            return count($dirty) > 0;
        }

        foreach ((array) $attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }

        return false;
    }

    public function getOriginal(string $key = null, mixed $default = null): mixed
    {
        if ($key) {
            return $this->original[$key] ?? $default;
        }

        return $this->original;
    }

    public function syncOriginal(): static
    {
        $this->original = $this->attributes;
        return $this;
    }

    public function newQuery(): Builder
    {
        return new Builder($this);
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            $dirty = $this->getDirty();
            if (count($dirty) > 0) {
                $this->exists = true;
            }
        } else {
            $this->exists = true;
            $this->wasRecentlyCreated = true;
        }

        return true;
    }

    public function update(array $attributes = []): bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->fill($attributes)->save();
    }

    public function delete(): bool
    {
        if ($this->exists) {
            $this->exists = false;
        }

        return true;
    }

    public function getKey(): mixed
    {
        return $this->getAttribute($this->primaryKey);
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getTable(): string
    {
        return $this->table ?: Str::plural(Str::snake(class_basename(static::class)));
    }

    public function getConnectionName(): string
    {
        return $this->connection;
    }

    public function newModelInstance(array $attributes = []): static
    {
        return new static($attributes);
    }

    public function toArray(): array
    {
        return array_merge(
            $this->attributesToArray(),
            $this->relationsToArray()
        );
    }

    public function attributesToArray(): array
    {
        return array_filter($this->attributes, function ($item) {
            return !is_null($item);
        });
    }

    public function relationsToArray(): array
    {
        return $this->relations;
    }

    public function setRelation(string $relation, mixed $value): static
    {
        $this->relations[$relation] = $value;
        return $this;
    }

    public function getRelation(string $relation): mixed
    {
        return $this->relations[$relation] ?? null;
    }

    public function relationLoaded(string $relation): bool
    {
        return array_key_exists($relation, $this->relations);
    }

    public function getTableColumns(): array
    {
        return $this->attributes;
    }

    public function getCasts(): array
    {
        return $this->casts;
    }

    public function getHidden(): array
    {
        return $this->hidden;
    }

    public function getVisible(): array
    {
        return $this->visible;
    }

    public function makeVisible(array|string $attributes): static
    {
        $this->hidden = array_diff($this->hidden, (array) $attributes);
        return $this;
    }

    public function makeHidden(array|string $attributes): static
    {
        $this->hidden = array_merge($this->hidden, (array) $attributes);
        return $this;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->getAttribute($key) !== null;
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }
}
