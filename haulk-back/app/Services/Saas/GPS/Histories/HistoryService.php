<?php

namespace App\Services\Saas\GPS\Histories;

use App\Exports\GPS\HistoryExport;
use App\Models\BodyShop\Settings\Settings;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Repositories\Saas\GPS\HistoryRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class HistoryService
{
    protected HistoryRepository $repo;

    public function __construct(HistoryRepository $repo)
    {
        $this->repo = $repo;
    }

    public function createEmptyData(Vehicle $vehicle, $deviceId = null): History
    {
        $model = new History();
        $model->received_at = CarbonImmutable::now();
        if($vehicle->isTruck()){
            $model->truck_id = $vehicle->id;
        } else {
            $model->trailer_id = $vehicle->id;
        }
        $model->driver_id = $vehicle->driver_id;
        $model->event_type = $vehicle instanceof Truck
            ? History::EVENT_ENGINE_OFF
            : History::EVENT_TRAILER_STOPPED;
        if($deviceId){
            $model->device_id = $deviceId;
        } else {
            $model->device_id = $vehicle->gps_device_id;
        }

        $model->company_id = $vehicle->carrier_id;
        $model->save();

        return $model;
    }

    public function export(Collection $collection, Company $company, array $filters)
    {
        try {
            $now = CarbonImmutable::now()->format('H-i-s');
            $time = null;
            if(data_get($filters, 'date_from')){
                $dateFrom = CarbonImmutable::parse(data_get($filters, 'date_from'))->format('m-d-Y');
            }
            $time = $dateFrom ?? null;
            if(!$time){
                $time = CarbonImmutable::now()->format('m-d-Y');
            }

            $name = "excel/gps_history_{$time}_{$now}.xlsx";

            if(Storage::disk('public')->exists($name)){
                Storage::disk('public')->delete($name);
            }

            Excel::store(new HistoryExport($collection, $company, $filters), $name,'public');

            return url("/storage/{$name}");
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function getAdditional(Collection $collection): array
    {
        return [
            'total_mileage' => $this->totalMileage($collection),
            'drivers' => $this->drivers($collection),
        ];
    }
    public function totalMileage(Collection $collection): ?float
    {
        if($collection->first() && $collection->last()){
            return $collection->last()->vehicle_mileage - $collection->first()->vehicle_mileage;
        }

        return null;
    }

    public function drivers(Collection $collection): Collection
    {
        $driver_id = array_diff(array_unique($collection->pluck('driver_id')->toArray()), [null]);

        return User::query()->whereIn('id', $driver_id)
            ->get();
    }

    // создает запись в истории gps, когда сменили водителя на траке/трейлере
    public function createIfAttachDriver(Vehicle $vehicle): void
    {
        $date = CarbonImmutable::now();

        $historyQuery = History::query();

        if($vehicle->isTruck()){
            $historyQuery->where('truck_id', $vehicle->id);
        } else {
            $historyQuery->where('trailer_id', $vehicle->id);
        }

        $history = $historyQuery->where('received_at', '<=', $date)
            ->orderByDesc('received_at')
            ->first();

        if($history){
            $this->createNewHistory($history, $vehicle, $vehicle->driver_id, $history->driver_id, $date);

            $nextRecs = History::query();
            if($vehicle->isTruck()){
                $nextRecs->where('truck_id', $vehicle->id);
            } else {
                $nextRecs->where('trailer_id', $vehicle->id);
            }

            $nextRecs->where('received_at', '>', $date)
                ->update(['driver_id' => $vehicle->driver_id]);
        }
    }

    // создает/обновляем записи в истории gps, когда водителя на траке/трейлере добавили историю  в прошлом
    public function createIfAddDriverHistory(
        Vehicle $vehicle,
        User $driver,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt
    )
    {
        $startAt = american_date_to_utc($startAt);
        $endAt = american_date_to_utc($endAt);

        $historyRecBefore = History::query()
//            ->where('truck_id', $vehicle->id)
            ->where(function(Builder $b) use ($vehicle) {
                if($vehicle->isTruck()){
                    return $b->where('truck_id', $vehicle->id);
                }
                return $b->where('trailer_id', $vehicle->id);
            })
            ->where('received_at', '<=', $startAt)
            ->orderByDesc('received_at')
            ->first();

        if(!$historyRecBefore){
            $historyRecBefore = History::query()
                ->where(function(Builder $b) use ($vehicle) {
                    if($vehicle->isTruck()){
                        return $b->where('truck_id', $vehicle->id);
                    }
                    return $b->where('trailer_id', $vehicle->id);
                })
                ->where('received_at', '>=', $startAt)
                ->where('received_at', '<=', $endAt)
                ->orderByDesc('received_at')
                ->first();
        }

        $historyRecAfter = History::query()
            ->where(function(Builder $b) use ($vehicle) {
                if($vehicle->isTruck()){
                    return $b->where('truck_id', $vehicle->id);
                }
                return $b->where('trailer_id', $vehicle->id);
            })
            ->where('received_at', '<=', $endAt)
            ->where('received_at', '>=', $startAt)
            ->orderByDesc('received_at')
            ->first();

        if($historyRecBefore && $historyRecAfter){

            logger_info( 'createIfAddDriverHistory 5');

            $newBefore = $this->createNewHistory($historyRecBefore, $vehicle, $driver->id, $historyRecBefore->driver_id, $startAt);
            $newAfter = $this->createNewHistory($historyRecAfter, $vehicle, $historyRecAfter->driver_id, $driver->id, $endAt);

            History::query()
                ->where(function(Builder $b) use ($vehicle) {
                    if($vehicle->isTruck()){
                        return $b->where('truck_id', $vehicle->id);
                    }
                    return $b->where('trailer_id', $vehicle->id);
                })
                ->whereBetween('received_at', [$startAt, $endAt])
                ->whereNotIn('id', [$newBefore->id, $newAfter->id])
                ->update(['driver_id' => $driver->id])
            ;
        } elseif (
            $historyRecBefore && !$historyRecAfter
        ){
            $newBefore = $this->createNewHistory($historyRecBefore, $vehicle, $driver->id, $historyRecBefore->driver_id, $startAt);

            History::query()
                ->where(function(Builder $b) use ($vehicle) {
                    if($vehicle->isTruck()){
                        return $b->where('truck_id', $vehicle->id);
                    }
                    return $b->where('trailer_id', $vehicle->id);
                })
                ->whereBetween('received_at', [$startAt, $endAt])
                ->whereNotIn('id', [$newBefore->id])
                ->update(['driver_id' => $driver->id])
            ;
        } elseif (
            !$historyRecBefore && $historyRecAfter
        ){
            $newAfter = $this->createNewHistory($historyRecAfter, $vehicle, $historyRecAfter->driver_id, $driver->id, $endAt);
            History::query()
                ->where(function(Builder $b) use ($vehicle) {
                    if($vehicle->isTruck()){
                        return $b->where('truck_id', $vehicle->id);
                    }
                    return $b->where('trailer_id', $vehicle->id);
                })
                ->whereBetween('received_at', [$startAt, $endAt])
                ->whereNotIn('id', [$newAfter->id])
                ->update(['driver_id' => $driver->id]);
        }
    }


    private function createNewHistory(
        History $clone,
        Vehicle $vehicle,
        $driverId,
        $oldDriverId,
        CarbonImmutable $receivedAt
    ): History
    {
        $newHistory = new History();
        $newHistory->driver_id = $driverId;
        $newHistory->old_driver_id = $oldDriverId;
        $newHistory->received_at = $receivedAt;

        if($vehicle->isTruck()){
            $newHistory->truck_id = $vehicle->id;
        } else {
            $newHistory->trailer_id = $vehicle->id;
        }

        $newHistory->longitude = $clone->longitude;
        $newHistory->latitude = $clone->latitude;
        $newHistory->speed = $clone->speed;
        $newHistory->vehicle_mileage = $clone->vehicle_mileage;
        $newHistory->heading = $clone->heading;
        $newHistory->event_type = History::EVENT_CHANGE_DRIVER;
        $newHistory->device_id = $clone->device_id;
        $newHistory->company_id = $clone->company_id;
        $newHistory->device_battery_level = $clone->device_battery_level;
        $newHistory->device_battery_charging_status = $clone->device_battery_charging_status;
        $newHistory->save();

//        if($vehicle->last_gps_history_id == $clone->id){
//            $vehicle->last_gps_history_id = $newHistory->id;
//            $vehicle->save();
//        }

        return $newHistory;
    }

    public function getCoordsForRoute(
        array $filter,
        $companyId = null,
        $fromId = null
    ): array
    {
        $tmp = [];
        $count = 0;
        $c = 0;

        $data = History::query()
            ->select(\DB::raw("id, longitude, latitude, received_at, is_speeding"))
            ->filter($filter)
            ->where('company_id', $companyId ? $companyId : authUser()->getCompanyId())
            ->where('event_type', History::EVENT_DRIVING)
            ->when($fromId, function ($query) use ($fromId) {
                $query->where('id', '>', $fromId);
            })
            ->orderBy('received_at')
            ->get();

        // разбиваем все данные на массивы, где не более 99 эл. в массиве
        foreach ($data as $item){
            /** @var $item History */
            if($c != 0){
                if(
                    isset($tmp[$count][$c-1])
                    && $tmp[$count][$c-1]['speeding'] != $this->speeding($item)
                ){
                    $count++;
                } elseif(isset($tmp[$count]) && !(count($tmp[$count]) < 99)) {
                    $count++;
                }
            }

            $tmp[$count][$c] = [
                'id' => $item->id,
                'location' => [
                    'lat' => $item->latitude,
                    'lng' => $item->longitude,
                ],
                'speeding' => $this->speeding($item),
                'timestamp' => $item->received_at->timestamp
            ];
            $c++;
        }

        // чтоб линия маршрута была без разломов, мы в конец предыдущего массив копируем первый эл. текущего массива
        foreach ($tmp as $k => $t){
            if($k == 0) continue;

            array_push($tmp[$k - 1], [
                'id' => current($t)['id'],
                "location" =>  [
                    "lat" => current($t)['location']['lat'],
                    "lng" => current($t)['location']['lng']
                ],
                "speeding" => current($tmp[$k - 1])['speeding'],
                "timestamp" => current($t)['timestamp']
            ]);

            $tmp[$k] = array_values($t);
        }

        return $tmp;
    }

    private function speeding(History $item): bool
    {
        return  $item->is_speeding;
    }
}

