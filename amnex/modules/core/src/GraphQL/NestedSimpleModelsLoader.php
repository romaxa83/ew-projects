<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

class NestedSimpleModelsLoader extends SimpleModelsLoader
{
    public function load(EloquentCollection $parents): void
    {
        $relationParts = explode('.', $this->relation);

        $relations = $this->applyRecursivelyBuilder($relationParts);

        $parents->load($relations);
    }

    private function applyRecursivelyBuilder(array $relationParts): ?array
    {
        $subRelation = array_shift($relationParts);
        if (!$subRelation) {
            return null;
        }

        return [
            $subRelation => function ($b) use (&$relationParts) {
                call_user_func($this->decorateBuilder, $b);

                if ($sub = $this->applyRecursivelyBuilder($relationParts)) {
                    $b->with(array_key_first($sub), array_first($sub));
                }
            }
        ];
    }
}
