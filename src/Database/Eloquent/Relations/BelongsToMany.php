<?php

declare(strict_types=1);

namespace LoveGem\Database\Eloquent\Relations;

use LoveGem\Database\Eloquent\Model;
use LoveGem\Database\Eloquent\Builder;
use LoveGem\Database\Eloquent\Collection;

class BelongsToMany
{
    protected Model $parent;

    protected string $table;

    protected string $foreignPivotKey;

    protected string $relatedPivotKey;

    protected string $relation;

    protected Builder $query;

    public function __construct(
        Builder $query,
        Model $parent,
        string $table,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $relation
    ) {
        $this->query = $query;
        $this->parent = $parent;
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->relation = $relation;
    }

    public function getResults(): Collection
    {
        $query = $this->query
            ->where($this->relatedPivotKey, $this->parent->getKey());

        return $query->get();
    }

    public function addEagerConstraints(array $models): void
    {
        $keys = array_map(function ($model) {
            return $model->getKey();
        }, $models);

        $this->query->whereIn($this->relatedPivotKey, $keys);
    }

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, new Collection());
        }

        return $models;
    }

    public function match(array $models, Collection $results, string $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->getKey();

            $model->setRelation(
                $relation,
                $dictionary[$key] ?? new Collection()
            );
        }

        return $models;
    }

    protected function buildDictionary(Collection $results): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $key = $result->getAttribute($this->relatedPivotKey);

            $dictionary[$key][] = $result;
        }

        return $dictionary;
    }

    public function attach(mixed $ids, array $attributes = []): void
    {
        $records = [];

        foreach ((array) $ids as $id) {
            $records[] = array_merge([
                $this->foreignPivotKey => $this->parent->getKey(),
                $this->relatedPivotKey => $id,
            ], $attributes);
        }

        $this->insert($records);
    }

    public function detach(mixed $ids = null): int
    {
        $query = $this->newPivotQuery();

        if (!is_null($ids)) {
            $query->whereIn($this->relatedPivotKey, (array) $ids);
        }

        return $query->delete();
    }

    public function toggle(mixed $ids): void
    {
        foreach ((array) $ids as $id) {
            $query = $this->newPivotQuery()
                ->where($this->relatedPivotKey, $id);

            if ($query->exists()) {
                $query->delete();
            } else {
                $this->attach($id);
            }
        }
    }

    protected function insert(array $records): bool
    {
        return (bool) $this->query->newQuery()
            ->table($this->table)
            ->insert($records);
    }

    protected function newPivotQuery(): Builder
    {
        return $this->query->newQuery()
            ->table($this->table)
            ->where($this->foreignPivotKey, $this->parent->getKey());
    }
}
