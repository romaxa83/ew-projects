<?php

namespace App\ModelFilters\VehicleDB;

use App\Models\VehicleDB\VehicleMake;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class VehicleMakeFilter extends ModelFilter
{
    /**
     * @param string $name
     * @return VehicleMakeFilter
     */
    public function s(string $name)
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }
}
