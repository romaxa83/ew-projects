<?php

namespace App\ModelFilters\BodyShop\TypesOfWork;

use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TypeOfWorkFilter
 *
 * @mixin TypeOfWork
 *
 * @package App\ModelFilters\BodyShop\TypesOfWork
 */
class TypeOfWorkFilter extends ModelFilter
{
    public function q(string $name)
    {
        $searchString = '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%';
        $this->whereRaw('lower(name) like ?', [$searchString]);
    }

    public function searchid(int $id)
    {
        $this->where('id', $id);
    }

    public function inventory(int $inventoryId)
    {
        $this->whereHas(
            'inventories',
            fn(Builder $q) => $q->where('inventory_id', $inventoryId)
        );
    }
}
