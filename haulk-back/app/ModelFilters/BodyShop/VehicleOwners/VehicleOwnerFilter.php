<?php

namespace App\ModelFilters\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class VehicleOwnerFilter
 *
 * @mixin VehicleOwner
 *
 * @package App\ModelFilters\BodyShop\VehicleOwners
 */
class VehicleOwnerFilter extends ModelFilter
{
    public function q(string $name)
    {
        $this->where(
            function (Builder $query) use ($name) {
                return $query
                    ->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(email) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(phone) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
            }
        );
    }

    public function tag(int $tagId): void
    {
        $this->whereHas(
            'tags',
            fn(Builder $query) => $query->where('id', $tagId)
        );
    }

    public function searchid(int $id): void
    {
        $this->where('id', $id);
    }
}
