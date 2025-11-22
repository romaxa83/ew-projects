<?php

namespace App\ModelFilters\Vehicles;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

abstract class VehicleFilter extends ModelFilter
{
    public function q(string $name)
    {
        $search = '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%';
        $this->where(
            function (Builder $query) use ($search) {
                return $query
                    ->whereRaw('lower(vin) like ?', [$search])
                    ->orWhereRaw('lower(unit_number) like ?', [$search])
                    ->orWhereRaw('lower(license_plate) like ?', [$search])
                    ->orWhereRaw('lower(temporary_plate) like ?', [$search])
                    ->orWhereHas(
                        'customer',
                        fn(Builder $q) => $q->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$search])
                    )
                    ->orWhereHas(
                        'owner',
                        fn(Builder $q) => $q->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$search])
                    );
            }
        );
    }

    public function search(string $value)
    {
        $search = '%' . escapeLike(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->where(
            function (Builder $query) use ($search) {
                return $query
                    ->whereRaw('lower(unit_number) like ?', [$search])
                    ->orWhereHas(
                        'driver',
                        fn(Builder $q) => $q->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', [$search])
                    );
            }
        );
    }

    public function company(int $company_id): void
    {
        $this->where(
            function (Builder $query) use ($company_id) {
                return $query->where('carrier_id', $company_id)
                    ->orWhere('broker_id', $company_id);
            }
        );
    }

    public function driver(int $driverId): void
    {
        $this->whereHas('driver', fn(Builder $q) => $q->where('id', $driverId));
    }

    public function owner(int $ownerId): void
    {
        $this->where('owner_id', $ownerId);
    }

    public function customer(int $customerId): void
    {
        $this->where('customer_id', $customerId);
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

    public function deviceStatuses(array $values): void
    {
        $this->whereHas('gpsDeviceWithTrashed', function(Builder $b) use ($values) {
            $b->whereIn('status', $values);
        });
    }
}
