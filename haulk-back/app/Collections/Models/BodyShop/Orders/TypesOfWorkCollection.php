<?php

namespace App\Collections\Models\BodyShop\Orders;

use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Collection;

/**
* @property-read TypeOfWork[] $items
 */
class TypesOfWorkCollection extends Collection implements DiffableInterface
{
    use Diffable;

    public function getAttributesForDiff(): array
    {
        $resultGetDirty = [];
        foreach ($this->items as $diffable) {
            $resultGetDirty[$diffable->id] = $diffable->toArray();
            foreach ($diffable->getRelationsForDiff() as $relationName => $relation) {
                if(is_array($relation)) {
                    $resultGetDirty[$diffable->id][$relationName] = $relation;
                } elseif($relation) {
                    $resultGetDirty[$diffable->id][$relationName] = $relation->getAttributesForDiff();
                }
            }
        }
        return $resultGetDirty;
    }
}
