<?php

namespace App\Collections\Models\BodyShop\Orders;

use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Collection;

/**
* @property-read TypeOfWorkInventory[] $items
 */
class TypesOfWorkInventoryCollection extends Collection implements DiffableInterface
{
    use Diffable;

    public function getAttributesForDiff(): array
    {
        $resultGetDirty = [];
        foreach ($this->items as $diffable) {
            $resultGetDirty[$diffable->id] = $diffable->toArray();
        }
        return $resultGetDirty;
    }

}
