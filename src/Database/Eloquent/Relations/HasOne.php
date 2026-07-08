<?php

declare(strict_types=1);

namespace LoveGem\Database\Eloquent\Relations;

use LoveGem\Database\Eloquent\Model;
use LoveGem\Database\Eloquent\Builder;
use LoveGem\Database\Eloquent\Collection;

class HasOne
{
    protected Model $parent;

    protected string $foreignKey;

    protected string $localKey;

    protected Builder $query;

    public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function getResults(): ?Model
    {
        return $this->query->where(
            $this->foreignKey,
            $this->getParentKey()
        )->first();
    }

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    public function match(array $models, Collection $results, string $relation): array
    {
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);

            $match = $results->first(function ($result) use ($key) {
                return $result->getAttribute($this->foreignKey) === $key;
            });

            $model->setRelation($relation, $match);
        }

        return $models;
    }

    protected function getParentKey(): mixed
    {
        return $this->parent->getAttribute($this->localKey);
    }

    public function addEagerConstraints(array $models): void
    {
        $this->query->whereIn(
            $this->foreignKey,
            $this->getEagerModelKeys($models)
        );
    }

    protected function getEagerModelKeys(array $models): array
    {
        $keys = [];

        foreach ($models as $model) {
            $keys[] = $model->getAttribute($this->localKey);
        }

        return array_filter($keys);
    }
}
