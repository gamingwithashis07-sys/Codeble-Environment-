<?php

declare(strict_types=1);

namespace LoveGem\Database\Eloquent\Relations;

use LoveGem\Database\Eloquent\Model;
use LoveGem\Database\Eloquent\Builder;
use LoveGem\Database\Eloquent\Collection;

class HasMany extends HasOne
{
    public function getResults(): Collection
    {
        return $this->query->where(
            $this->foreignKey,
            $this->getParentKey()
        )->get();
    }

    public function match(array $models, Collection $results, string $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);

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
            $key = $result->getAttribute($this->foreignKey);

            $dictionary[$key][] = $result;
        }

        return $dictionary;
    }
}
