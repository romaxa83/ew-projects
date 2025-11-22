<?php


namespace App\Collections\Models\Orders;


use App\Models\DiffableInterface;
use App\Models\Files\Media;
use App\Models\Orders\Vehicle;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Collection;

/**
* @property-read Media[] $items
 */
class MediaCollection extends Collection implements DiffableInterface
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
