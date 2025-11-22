<?php

namespace App\ModelFilters\GPS;

use App\Models\BodyShop\Settings\Settings;
use App\Models\GPS\Alert;
use App\Models\VehicleDB\VehicleMake;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class AlertFilter extends ModelFilter
{
    public function vehicleUnitNumber(string $value): void
    {
        // laravel не поддерживает whereHas между бд, приходиться делать доп. запросы
        $truckIds = Truck::query()
            ->select(['id'])
            ->whereRaw(
            'lower(' . Truck::TABLE_NAME . '.unit_number) like ?',
                [escapeLike(mb_convert_case($value, MB_CASE_LOWER)) . '%']
            )
            ->toBase()
            ->get()
            ->pluck('id')
            ->toArray()
        ;

        $trailerIds = Trailer::query()
            ->select(['id'])
            ->whereRaw(
                'lower(' . Trailer::TABLE_NAME . '.unit_number) like ?',
                [escapeLike(mb_convert_case($value, MB_CASE_LOWER)) . '%']
            )
            ->toBase()
            ->get()
            ->pluck('id')
            ->toArray()
        ;

        $this->where(function(Builder $q) use ($truckIds, $trailerIds){
            $q->whereIn('truck_id', $truckIds)
                ->orWhereIn('trailer_id', $trailerIds);
        });
    }

    public function driver(int $driverId): void
    {
        $this->where('driver_id', $driverId);
    }

    public function truck(int $truckId): void
    {
        $this->where('truck_id', $truckId);
    }

    public function trailer(int $trailerId): void
    {
        $this->where('trailer_id', $trailerId);
    }

    public function device(int $value): void
    {
        $this->where('device_id', $value);
    }

    public function alertType($alertType): self
    {
        if(is_array($alertType)){
            return $this->whereIn('alert_type', $alertType);
        }

        return $this->where('alert_type', $alertType);
    }

    public function date(string $value): self
    {
        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $dateFrom = (new CarbonImmutable($value, $timeZone))->startOfDay()->setTimezone('UTC');
        $dateTo = (new CarbonImmutable($value, $timeZone))->endOfDay()->setTimezone('UTC');


        return $this->where('received_at', '>=', $dateFrom)
            ->where('received_at', '<=', $dateTo)
            ;
    }
}
