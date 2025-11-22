<?php

namespace App\ModelFilters\Vehicles;

use App\Foundations\Models\BaseModelFilter;
use Illuminate\Database\Eloquent\Builder;

class VehicleFilter extends BaseModelFilter
{
    public function search(string $value)
    {
        $search = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->where(
            function (Builder $query) use ($search) {
                return $query
                    ->whereRaw('lower(vin) like ?', [$search])
                    ->orWhereRaw('lower(unit_number) like ?', [$search])
                    ->orWhereRaw('lower(license_plate) like ?', [$search])
                    ->orWhereRaw('lower(temporary_plate) like ?', [$search])
                    ->orWhereHas('customer',
                        fn(Builder $q) => $q->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$search])
                    )
//                    ->orWhereHas(
//                        'owner',
//                        fn(Builder $q) => $q->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$search])
//                    )
                    ;
            }
        );
    }

    public function tag(int|string $value): void
    {
        $this->whereHas('tags',
            fn(Builder $query) => $query->where('id', $value)
        );
    }

    public function customer(int|string $value): void
    {
        $this->where('customer_id', $value);
    }
}
