<?php

namespace App\Services\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\TrailerDriverHistory;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\TruckDriverHistory;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\Vehicle\VehicleEventService;
use App\Services\Saas\GPS\Histories\HistoryService;
use Carbon\CarbonImmutable;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class DriverHistoryService
{
    private HistoryService $gpsHistoryService;

    public function __construct(HistoryService $gpsHistoryService)
    {
        $this->gpsHistoryService = $gpsHistoryService;
    }

    public function add(Vehicle $vehicle, User $driver, array $data): Vehicle
    {
        try {
            DB::beginTransaction();

            if($vehicle->isTruck()){
                $this->addToTruck($vehicle, $driver, $data);
            } else {
                $this->addToTrailer($vehicle, $driver, $data);
            }

            DB::commit();
            return $vehicle->refresh();
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    protected function addToTruck(Truck $truck, User $driver, array $data): void
    {
        $startAt = CarbonImmutable::make($data['start_at']);
        $endAt = CarbonImmutable::make($data['end_at']);

        logger_info('ADD TO TRUCK',[
            'startAt' => $startAt,
            'endAt' => $endAt,
        ]);

        // проверяем был ли текущий водитель на другом траке, в переданный период времени
        $anotherHistoryThisDriver = TruckDriverHistory::query()
            ->with('vehicle')
            ->where('driver_id', $driver->id)
            ->where('truck_id', '!=', $truck->id)
            ->get();

        $existAnotherTruck = null;
        foreach ($anotherHistoryThisDriver as $item){
            /** @var $item TruckDriverHistory */
            if($existAnotherTruck == null){
                $unassigned_at = $item->unassigned_at ?? CarbonImmutable::now();
                // стартовая дата попадает в промежуток
                if(
                    $existAnotherTruck == null
                    && ($item->assigned_at < $startAt && $startAt < $unassigned_at)
                ){
                    $existAnotherTruck = $item->vehicle;
                }
                // конечная дата попадает в промежуток
                if(
                    $existAnotherTruck == null
                    && ($item->assigned_at < $endAt && $endAt < $unassigned_at)
                ){

                    $existAnotherTruck = $item->vehicle;
                }

            }
        }

        if($existAnotherTruck){
            throw ValidationException::withMessages([
                'start_at' => __("exceptions.user.driver.history.driver_assigned_another_truck", [
                    'unit_number' => $existAnotherTruck->unit_number
                ])
            ]);
        }

        // проверяем не является ли это редактирование записи
        $historyEdit = TruckDriverHistory::query()
            ->where('truck_id', $truck->id)
            ->where('driver_id', $driver->id)
            ->where(function (Builder $b) use ($startAt, $endAt){
                $b->whereBetween('assigned_at', [$startAt, $endAt])
                    ->orWhereBetween('unassigned_at', [$startAt, $endAt]);
            })
            ->first()
        ;
        if($historyEdit){
            $historyEdit->update([
                'assigned_at' => $startAt,
                'unassigned_at' => $endAt,
            ]);
        }

        // выбираем записи которые попадают в диапазон (переданного периода), "до" и "после"
        $historyRecs = TruckDriverHistory::query()
            ->where('truck_id', $truck->id)
            ->get();
        $recBefore = null;
        $recAfter = null;

        foreach ($historyRecs as $rec){
            if($recBefore && $recAfter) break;
            /** @var $rec TruckDriverHistory */
            $unassigned_at = $rec->unassigned_at ?? CarbonImmutable::now();
            if(
                $recBefore == null
                && ($rec->assigned_at < $startAt && $startAt < $unassigned_at)
            ){
                $recBefore = $rec;
            }
            if(
                $recAfter == null
                && ($rec->assigned_at < $endAt && $endAt < $unassigned_at)
            ){
                $recAfter = $rec;
            }
        }

        if(
            $recAfter
            && $recBefore
            && $recAfter === $recBefore
            && $recBefore->driver_id == $driver->id
            && $recAfter->driver_id == $driver->id
        ){
            // case-0, у нас есть запись до и после, и это одна и таже запись, при этом по данному водителю, т.е. происходит редактирование
            // удалеяем данную запись, новая будет создана ниже
            $recAfter->delete();

        } elseif(
            $recAfter
            && $recBefore
            && $recAfter === $recBefore
        ){
            // case-1, у нас есть запись до и после, и это одна и таже запись, мы вклиниваем свою запись и раздваиваем существующую
            // создаем запись "до", а сущесвующую меняем, делая ее "после"
            $newRecBefore = new TruckDriverHistory();
            $newRecBefore->driver_id = $recAfter->driver_id;
            $newRecBefore->truck_id = $truck->id;
            $newRecBefore->assigned_at = $recAfter->assigned_at;
            $newRecBefore->unassigned_at = $startAt;
            $newRecBefore->save();

            $recAfter->update(['assigned_at' => $endAt]);
        } elseif (
            $recAfter
            && $recBefore
            && $recAfter !== $recBefore
        ) {
            // case-2, у нас есть запись "до" и "после", но они не совпадают, им просто меняем данные
            $recBefore->update(['unassigned_at' => $startAt]);
            $recAfter->update(['assigned_at' => $endAt]);
        } elseif (
            !$recAfter
            && $recBefore
        ){
            // case-3, у нас есть запись "до" но нет "после", меня только запись "до"
            $recBefore->update(['unassigned_at' => $startAt]);
        } elseif (
            $recAfter
            && !$recBefore
        ){
            // case-4, у нас есть запись "после" но нет "до", меня только запись "после"
            $recAfter->update(['assigned_at' => $endAt]);
        }

        // создаем нашу запись, если это было не редактирование
        if(!$historyEdit){
            $history = new TruckDriverHistory();
            $history->driver_id = $driver->id;
            $history->truck_id = $truck->id;
            $history->assigned_at = $startAt;
            $history->unassigned_at = $endAt;
            $history->save();
        }

        // добавляем/меняем запись в истории трекинга
        $this->gpsHistoryService->createIfAddDriverHistory($truck, $driver, $startAt, $endAt);

        // создаем запись в истории изменений
        (new VehicleEventService($truck))->user(authUser())->update(null, true);
    }

    protected function addToTrailer(Trailer $trailer, User $driver, array $data): void
    {
        $startAt = CarbonImmutable::make($data['start_at']);
        $endAt = CarbonImmutable::make($data['end_at']);

        // проверяем был ли текущий водитель на другом траке, в переданный период времени
        $anotherHistoryThisDriver = TrailerDriverHistory::query()
            ->with('vehicle')
            ->where('driver_id', $driver->id)
            ->where('trailer_id', '!=', $trailer->id)
            ->get();

        $existAnotherTrailer = null;
        foreach ($anotherHistoryThisDriver as $item){
            /** @var $item TrailerDriverHistory */
            if($existAnotherTrailer == null){
                $unassigned_at = $item->unassigned_at ?? CarbonImmutable::now();
                // стартовая дата попадает в промежуток
                if(
                    $existAnotherTrailer == null
                    && ($item->assigned_at < $startAt && $startAt < $unassigned_at)
                ){
                    $existAnotherTrailer = $item->vehicle;
                }
                // конечная дата попадает в промежуток
                if(
                    $existAnotherTrailer == null
                    && ($item->assigned_at < $endAt && $endAt < $unassigned_at)
                ){
                    $existAnotherTrailer = $item->vehicle;
                }

            }
        }

        if($existAnotherTrailer){
            throw ValidationException::withMessages([
                'start_at' => __("exceptions.user.driver.history.driver_assigned_another_trailer", [
                    'unit_number' => $existAnotherTrailer->unit_number
                ])
            ]);
        }

        // проверяем не является ли это редактирование записи
        $historyEdit = TrailerDriverHistory::query()
            ->where('trailer_id', $trailer->id)
            ->where('driver_id', $driver->id)
            ->where(function (Builder $b) use ($startAt, $endAt){
                $b->whereBetween('assigned_at', [$startAt, $endAt])
                    ->orWhereBetween('unassigned_at', [$startAt, $endAt]);
            })
            ->first()
        ;
        if($historyEdit){
            $historyEdit->update([
                'assigned_at' => $startAt,
                'unassigned_at' => $endAt,
            ]);
        }

        // выбираем записи которые попадают в диапазон (переданного периода), "до" и "после"
        $historyRecs = TrailerDriverHistory::query()
            ->where('trailer_id', $trailer->id)
            ->get();
        $recBefore = null;
        $recAfter = null;
        foreach ($historyRecs as $rec){
            if($recBefore && $recAfter) break;
            /** @var $rec TrailerDriverHistory */
            $unassigned_at = $rec->unassigned_at ?? CarbonImmutable::now();
            if(
                $recBefore == null
                && ($rec->assigned_at < $startAt && $startAt < $unassigned_at)
            ){
                $recBefore = $rec;
            }
            if(
                $recAfter == null
                && ($rec->assigned_at < $endAt && $endAt < $unassigned_at)
            ){
                $recAfter = $rec;
            }
        }

        if(
            $recAfter
            && $recBefore
            && $recAfter === $recBefore
            && $recBefore->driver_id == $driver->id
            && $recAfter->driver_id == $driver->id
        ){
            // case-0, у нас есть запись до и после, и это одна и таже запись, при этом по данному водителю, т.е. происходит редактирование
            // удалеяем данную запись, новая будет создана ниже
            $recAfter->delete();
        } elseif(
            $recAfter
            && $recBefore
            && $recAfter === $recBefore
        ){
            // case-1, у нас есть запись до и после, и это одна и таже запись, мы вклиниваем свою запись и раздваиваем существующую
            // создаем запись "до", а сущесвующую меняем, делая ее "после"
            $newRecBefore = new TrailerDriverHistory();
            $newRecBefore->driver_id = $recAfter->driver_id;
            $newRecBefore->trailer_id = $trailer->id;
            $newRecBefore->assigned_at = $recAfter->assigned_at;
            $newRecBefore->unassigned_at = $startAt;
            $newRecBefore->save();

            $recAfter->update(['assigned_at' => $endAt]);
        } elseif (
            $recAfter
            && $recBefore
            && $recAfter !== $recBefore
        ) {
            // case-2, у нас есть запись "до" и "после", но они не совпадают, им просто меняем данные
            $recBefore->update(['unassigned_at' => $startAt]);
            $recAfter->update(['assigned_at' => $endAt]);
        } elseif (
            !$recAfter
            && $recBefore
        ){
            // case-3, у нас есть запись "до" но нет "после", меня только запись "до"
            $recBefore->update(['unassigned_at' => $startAt]);
        } elseif (
            $recAfter
            && !$recBefore
        ){
            // case-4, у нас есть запись "после" но нет "до", меня только запись "после"
            $recAfter->update(['assigned_at' => $endAt]);
        }

        // создаем нашу запись
        if(!$historyEdit) {
            $history = new TrailerDriverHistory();
            $history->driver_id = $driver->id;
            $history->trailer_id = $trailer->id;
            $history->assigned_at = $startAt;
            $history->unassigned_at = $endAt;
            $history->save();
        }

        $this->gpsHistoryService->createIfAddDriverHistory($trailer, $driver, $startAt, $endAt);

        (new VehicleEventService($trailer))->user(authUser())->update(null, true);
    }
}
