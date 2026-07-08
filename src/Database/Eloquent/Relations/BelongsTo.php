<?php

declare(strict_types=1);

namespace LoveGem\Database\Eloquent\Relations;

use LoveGem\Database\Eloquent\Model;
use LoveGem\Database\Eloquent\Builder;
use LoveGem\Database\Eloquent\Collection;

class BelongsTo
{
    protected Model $child;

    protected string $foreignKey;

    protected string $ownerKey;

    protected string $relation;

    protected Builder $query;

    public function __construct(Builder $query, Model $child, string $foreignKey, string $ownerKey, string $relation)
    {
        $this->query = $query;
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->relation = $relation;
    }

    public function getResults(): ?Model
    {
        return $this->query->where(
            $this->ownerKey,
            $this->getChildKey()
        )->first();
    }

    protected function getChildKey(): mixed
    {
        return $this->child->getAttribute($this->foreignKey);
    }

    public function addEagerConstraints(array $models): void
    {
        $this->query->whereIn(
            $this->ownerKey,
            $this->getEagerModelKeys($models)
        );
    }

    protected function getEagerModelKeys(array $models): array
    {
        $keys = [];

        foreach ($models as $model) {
            $keys[] = $model->getAttribute($this->foreignKey);
        }

        return array_filter($keys);
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
            $key = $model->getAttribute($this->foreignKey);

            $match = $results->first(function ($result) use ($key) {
                return $result->getAttribute($this->ownerKey) === $key;
            });

            $model->setRelation($relation, $match);
        }

        return $models;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getOwnerKey(): string
    {
        return $this->ownerKey;
    }

    public function getRelation(): string
    {
        return $this->relation;
    }
}
