<?php

namespace App\ModelFilters\VehicleDB;

use App\Models\VehicleDB\VehicleMake;
use App\Models\VehicleDB\VehicleModel;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class VehicleModelFilter extends ModelFilter
{
    /**
     * @param string $type
     * @return VehicleModelFilter
     */
    public function makeName($make_name)
    {
        return $this->where(function (Builder $query) use ($make_name) {
            $query->whereHas(
                'make',
                function (Builder $q) use ($make_name) {
                    $q->whereRaw(
                        'lower(' . VehicleMake::TABLE_NAME . '.name) like ?',
                        ['%' . escapeLike(mb_convert_case($make_name, MB_CASE_LOWER)) . '%']
                    );
                }
            );
        });
    }

    /**
     * @param string $name
     * @return VehicleModelFilter
     */
    public function s(string $name)
    {
        return $this->whereRaw(
            'lower(' . VehicleModel::TABLE_NAME . '.name) like ?',
            [
                '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'
            ]
        );
    }
}
