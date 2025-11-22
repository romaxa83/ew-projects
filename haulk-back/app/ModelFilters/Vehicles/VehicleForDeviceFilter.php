<?php

namespace App\ModelFilters\Vehicles;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class VehicleForDeviceFilter extends ModelFilter
{
    public function search(string $search): void
    {
        $query = escapeLike(mb_convert_case($search, MB_CASE_LOWER));

        $this->where(
            static function (Builder $b) use ($query) {
                $b
                    ->orWhereRaw(sprintf("LOWER(%s) LIKE ?",  'unit_number'), ["%$query%"])
                    ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", 'vin'), ["$query%"])
                ;
            }
        );
    }

    public function company($value)
    {
        $this->where('carrier_id', $value);
    }
}

