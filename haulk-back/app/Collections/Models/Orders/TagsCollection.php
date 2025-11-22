<?php


namespace App\Collections\Models\Orders;


use App\Models\DiffableInterface;
use App\Models\Tags\Tag;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read Tag[] $items
 */
class TagsCollection extends Collection implements DiffableInterface
{
    use Diffable;

    public function getAttributesForDiff(): array
    {
        $resultGetDirty = [];

        foreach ($this->items as $diffable) {
            $resultGetDirty[$diffable->id] = $diffable->getAttributesForDiff();
        }

        return $resultGetDirty;
    }
}
