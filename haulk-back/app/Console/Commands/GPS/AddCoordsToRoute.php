<?php

namespace App\Console\Commands\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\GPS\Route;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Google\Commands\Road\GetRouteCommand;
use App\Services\Saas\GPS\Histories\HistoryService;
use App\Services\Telegram\Telegram;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
class AddCoordsToRoute extends Command
{
    protected $reqCount = 0;

    protected $signature = 'gps:add_coords {--date=}';

    protected $description = 'Добавление координат к текущим маршрутам';

    protected HistoryService $service;

    public function __construct(HistoryService $service)
    {
        parent::__construct();

        $this->service = $service;
    }


    public function handle(): void
    {
        $now = CarbonImmutable::now();
        $date = $this->option('date') ?? $now->format('Y-m-d');

        try {
            $start = microtime(true);

            $this->exec($date);

            $time = microtime(true) - $start;

        } catch (\Throwable $e) {
            Telegram::error('AddCoordsToRoute ERROR', authUser()->email ?? null, [
                'msg' => $e->getMessage()
            ]);
        }

        $this->info("Done [time = {$time}]");

//        Telegram::info('AddCoordsToRoute - final', null, [
//            'date' => $date,
//            'time' => $time
//        ]);
    }
    private function exec($date)
    {
        $this->createRoute($date);

        $yesterday = CarbonImmutable::yesterday()->format('Y-m-d');
        $this->fillRoute($yesterday, true);

        $this->fillRoute($date);
    }
    protected function createRoute($date): bool
    {
        $res = [];

        $trucks = Truck::query()
            ->whereNotNull('gps_device_id')
            ->whereHas('gpsDevice', function ($query){
                $query->where('status', DeviceStatus::ACTIVE());
            })
            ->get()
            ->pluck('gps_device_id', 'id')
        ;

        foreach($trucks as $truckId => $deviceId){
            if(
                !Route::query()
                    ->whereDate('date', $date)
                    ->where('truck_id', $truckId)
                    ->where('device_id', $deviceId)
                    ->exists()
            ){
                $route = new Route();
                $route->truck_id = $truckId;
                $route->device_id = $deviceId;
                $route->date = $date;
                $route->data = [];
                $route->save();

                $res[] = sprintf("route_id = [%s], device_id = [%s], truck_id = [%s]",$route->id, $deviceId, $truckId);
            }
        }

        $trailers = Trailer::query()
            ->whereNotNull('gps_device_id')
            ->whereHas('gpsDevice', function ($query){
                $query->where('status', DeviceStatus::ACTIVE());
            })
            ->get()
            ->pluck('gps_device_id', 'id')
        ;

        foreach($trailers as $trailerId => $deviceId){
            if(
                !Route::query()
                    ->whereDate('date', $date)
                    ->where('trailer_id', $trailerId)
                    ->where('device_id', $deviceId)
                    ->exists()
            ){
                $route = new Route();
                $route->trailer_id = $trailerId;
                $route->device_id = $deviceId;
                $route->date = $date;
                $route->data = [];
                $route->save();

                $res[] = sprintf("route_id = [%s], device_id = [%s], trailer_id = [%s]",$route->id, $deviceId, $trailerId);
            }
        }

        if(!empty($res)){
//            Telegram::info('🧭 AddCoordsToRoute - create route', authUser()->email ?? null, $res);
        }

        return !empty($res);
    }
    protected function fillRoute($date, bool $yesterday = false)
    {
        Route::query()
            ->whereDate('date', $date)
            ->each(function (Route $route) use ($date, $yesterday) {
                $filter = [
                    "date_from" => $date,
                    "date_to" => $date
                ];
                $track['device_id'] = $route->device->id;
                $track['date'] = $date;

                if($route->truck_id){
                    $filter["truck_id"] = $route->truck_id;
                    $track['truck_id'] = $route->truck_id;
                } else {
                    $filter["trailer_id"] = $route->trailer_id;
                    $track['trailer_id'] = $route->trailer_id;
                }

                $fromId = $route->last_point_id;
                $coords = $this->service
                    ->getCoordsForRoute($filter, $route->device->company_id, $fromId);

                if(!empty($coords)){
                    $lastPointId = last(last($coords))['id'] ?? null;

                    // тут процесс запросов к гуглу, для получения нормализованных (привязанных к дороге) координат
                    $this->processRequest($route, $coords);
                    $route->update(['last_point_id' => $lastPointId]);

                    $track['google_api_request'] = $this->reqCount;

                    $msg = $yesterday
                        ? '🚀 AddCoordsToRoute - fill coords [yesterday]'
                        : '🚀 AddCoordsToRoute - fill coords'
                    ;
//                    Telegram::info($msg, authUser()->email ?? null, $track);
                }
            })
        ;
    }
    private function processRequest (
        Route $route,
        array $coords
    )
    {
        /** @var $command GetRouteCommand */
        $command = resolve(GetRouteCommand::class);

        foreach ($coords as $k => $item) {
            $key = bool_as_string(current($item)['speeding']);

            $tmp = $route->data;
            $tmp[] = [
                "$key-$k" => $command->handler($item)
            ];

            $this->reqCount++;

            $route->data = $tmp;
            $route->update();
        }
    }
}
