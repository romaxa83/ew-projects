<?php

namespace App\ModelFilters\Saas\GPS\Devices;

use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class DeviceFilter extends ModelFilter
{
    public function id($value)
    {
        if(is_array($value)){
            return $this->whereIn('id', $value);
        }

        return $this->where('id', $value);
    }

    public function query(string $query): void
    {
        $query = escapeLike(mb_convert_case($query, MB_CASE_LOWER));

        $this->where(
            static function (Builder $b) use ($query) {
                $b
                    ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", Device::TABLE_NAME . '.imei'), ["%$query%"])
                    ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", Device::TABLE_NAME . '.phone'), ["$query%"])
                    ->orWhereHas('truck', function(Builder $b) use ($query) {
                        $b->whereRaw(sprintf("LOWER(%s) LIKE ?", Truck::TABLE_NAME . '.unit_number'), ["%$query%"]);
                    })
                    ->orWhereHas('trailer', function(Builder $b) use ($query) {
                        $b->whereRaw(sprintf("LOWER(%s) LIKE ?", Trailer::TABLE_NAME . '.unit_number'), ["%$query%"]);
                    })
                ;
            }
        );
    }

    public function company(int $companyId): void
    {
        $this->where('company_id', $companyId);
    }

    public function status(string $value): void
    {
        $this->where('status', $value);
    }

    public function statuses(array $value): void
    {
        $this->whereIn('status', $value);
    }

    public function statusRequest(string $value): void
    {
        $this->where('status_request', $value);
    }

    public function phone(string $value): void
    {
        $this->whereRaw(sprintf("LOWER(%s) LIKE ?", 'phone'), ["$value%"]);
    }
}
