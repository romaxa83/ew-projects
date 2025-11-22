<?php

namespace App\Console\Commands\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\GPS\Route;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Google\Commands\RequestCommand;
use App\Services\Google\Commands\Road\GetRouteCommand;
use App\Services\Saas\GPS\Histories\HistoryService;
use App\Services\Telegram\Telegram;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GoogleRoadApiWorker extends Command
{
    protected $signature = 'worker:google_road {--date=} {--truck=} {--trailer=} {--device=}';
    protected HistoryService $service;

    protected $reqCount = 0;

    public function __construct(
        HistoryService $service
    )
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        $date = $this->option('date') ?? CarbonImmutable::now()->subDay()->format('m/d/Y');
        $truck = $this->option('truck');
        $trailer = $this->option('trailer');
        $device = $this->option('device');

        logger_info("[worker] GOOGLE ROAD API START", [
            'date' => $date,
            'trailer' => $trailer,
            'truck' => $truck,
            'device' => $device,
        ]);

        try {
            $start = microtime(true);

            $this->exec($date, $truck, $trailer);

            $time = microtime(true) - $start;

            logger_info("[worker] GOOGLE ROAD API [time = {$time}], [req = $this->reqCount]");
            $this->info("Done [time = {$time}], [req = $this->reqCount]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[worker] GOOGLE ROAD API FAIL', [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec($date, $truck, $trailer)
    {
        if($truck || $trailer){
            if($truck){
                $truck = Truck::query()
                    ->with([
                        'gpsDevice.truck',
                        'gpsDevice.company'
                    ])
                    ->where('id', $truck)
                    ->first();

                if($truck->gpsDevice){
                    logger_info("[worker] GOOGLE ROAD API exec truck [$truck->id]");
                    $this->forTruck($truck->gpsDevice, $date);
                }
            }
            if($trailer){
                $trailer = Trailer::query()
                    ->with([
                        'gpsDevice.trailer',
                        'gpsDevice.company'
                    ])
                    ->where('id', $trailer)
                    ->first();
                if($trailer->gpsDevice){
                    logger_info("[worker] GOOGLE ROAD API exec trailer [$trailer->id]");
                    $this->forTrailer($trailer->gpsDevice, $date);
                }
            }
        } else {
            logger_info("[worker] GOOGLE ROAD API exec system");
            $this->isWorker($date);
        }
    }

    private function isWorker(string $date): void
    {
        Device::query()
            ->has('truck')
            ->with(['truck', 'company'])
            ->where('status', DeviceStatus::ACTIVE())
            ->get()
            ->each(function(Device $item) use ($date) {
                $this->forTruck($item, $date);
            })
        ;

        Device::query()
            ->has('trailer')
            ->with(['trailer', 'company'])
            ->where('status', DeviceStatus::ACTIVE())
            ->get()
            ->each(function(Device $item) use ($date) {
                $this->forTrailer($item, $date);
            })
        ;
    }

//    private function forTruck(Device $device, string $date)
//    {
//        // получаем преобразованные координаты
//        $coords = $this->service->getCoordsForRoute([
//            "truck_id" => $device->truck->id,
//            "date_from" => $date,
//            "date_to" => $date
//        ], $device->company_id);
//
//        if(!empty($coords)){
//            /** @var $command RequestCommand */
//            $command = resolve(GetRouteCommand::class);
//            $hash = simple_hash($coords);
//
//            logger_info("[worker] GOOGLE ROAD API exec FOR TRUCK", [
//                "truck_id" => $device->truck->id,
//                "date_from" => $date,
//                "date_to" => $date,
//            ]);
//
//            if(
//                $route = Route::query()
//                    ->where('truck_id', $device->truck->id)
//                    ->whereDate('date', $date)
//                    ->first()
//            ){
//
//                dd($coords);
//
//                logger_info("[worker] GOOGLE ROAD API exec FOR TRUCK", [
//                    "route_hash" => $route->coords_hash,
//                    "new_hash" => $hash,
//                    "data_empty" => empty($route->data),
//                ]);
//
////                if($route->coords_hash == $hash)  return self::SUCCESS;
//                if(!empty($route->data))  return self::SUCCESS;
//
//                $route->data = [];
//                $route->coords_hash = $hash;
//                $route->save();
//            } else {
//                $route = $this->createEmptyRoute($device, $date, $hash);
//            }
//
////            Telegram::info('hi');
//
//            dd($route);
//
//            try {
//                $this->processRequest($route, $command, $coords);
//            } catch (\Throwable $e){
//                $route->delete();
//                throw new \Exception($e);
//            }
//        }
//    }

    private function forTruck(Device $device, string $date)
    {
        $track['device_id'] = $device->id;
        $track['truck_id'] = $device->truck->id;
        $track['date'] = $date;

        // получаем путь, для трака в конкретный день
        $route = Route::query()
            ->where('truck_id', $device->truck->id)
            ->whereDate('date', $date)
            ->first();

        // если у пути есть массив координат, то прекращаем работу, дополнение координатами
        // новыми точками для данного маршрута происходит в другом месте
        if(!empty($route->data))  return self::SUCCESS;

        // если его нет создаем пустой без координат
        if(!$route){
            $route = $this->createEmptyRoute($device, $date);
        }

        $track['route_id'] = $route->id;

        // получаем преобразованные координаты
        $coords = $this->service->getCoordsForRoute([
            "truck_id" => $device->truck->id,
            "date_from" => $date,
            "date_to" => $date
        ], $device->company_id);

        // если координат нет, прекращаем работу
        if(empty($coords)) return self::SUCCESS;

        // id последней точки координат, пишем в роут, чтоб при добавлении новых
        // координат отталкиваться от этой точки
        $lastPointId = last(last($coords))['id'] ?? null;
        $track['last_point_id'] = $lastPointId;

        $command = resolve(GetRouteCommand::class);
        try {
            // тут процесс запросов к гуглу, для получения нормализованных (привязанных к дороге) координат
            $this->processRequest($route, $command, $coords);
            $route->update(['last_point_id' => $lastPointId]);
        } catch (\Throwable $e){
            Telegram::error('GoogleRoadApiWorker', authUser()->email ?? null, [
                'msg' => $e->getMessage()
            ]);
            throw new \Exception($e);
        }

        $track['google_api_request'] = $this->reqCount;
        Telegram::info('GoogleRoadApiWorker', authUser()->email ?? null, $track);
    }

    public function createEmptyRoute(
        Device $device,
        string $date
    ): Route
    {
        $route = new Route();
        if($device->truck){
            $route->truck_id = $device->truck->id;
        }
        if($device->trailer){
            $route->trailer_id = $device->trailer->id;
        }
        $route->date = $date;
        $route->data = [];
        $route->device_id = $device->id;
        $route->save();

        return $route;
    }


//    private function forTrailer(Device $device, string $date)
//    {
//        $coords = $this->service->getCoordsForRoute([
//            "trailer_id" => $device->trailer->id,
//            "date_from" => $date,
//            "date_to" => $date
//        ], $device->company_id);
//
//        if(!empty($coords)){
//            /** @var $command RequestCommand */
//            $command = resolve(GetRouteCommand::class);
//            $hash = simple_hash($coords);
//
//            logger_info("[worker] GOOGLE ROAD API exec FOR TRAILER", [
//                "trailer_id" => $device->trailer->id,
//                "date_from" => $date,
//                "date_to" => $date,
//            ]);
//
//            if(
//                $route = Route::query()
//                    ->where('trailer_id', $device->trailer->id)
//                    ->whereDate('date', $date)
//                    ->first()
//            ){
//                logger_info("[worker] GOOGLE ROAD API exec FOR TRAILER", [
//                    "route_hash" => $route->coords_hash,
//                    "new_hash" => $hash,
//                    "data_empty" => empty($route->data),
//                ]);
////                if($route->coords_hash == $hash)  return self::SUCCESS;
//                if(!empty($route->data))  return self::SUCCESS;
//
//                $route->data = [];
//                $route->coords_hash = $hash;
//                $route->save();
//            } else {
//                $route = new Route();
//                $route->trailer_id = $device->trailer->id;
//                $route->date = $date;
//                $route->data = [];
//                $route->coords_hash = $hash;
//                $route->device_id = $device->id;
//                $route->save();
//            }
//
//            try {
//                $this->processRequest($route, $command, $coords);
//            } catch (\Throwable $e){
//                $route->delete();
//                throw new \Exception($e);
//            }
//        }
//    }

    private function forTrailer(Device $device, string $date)
    {
        $track['device_id'] = $device->id;
        $track['trailer_id'] = $device->trailer->id;
        $track['date'] = $date;

        // получаем путь, для трака в конкретный день
        $route = Route::query()
            ->where('trailer_id', $device->trailer->id)
            ->whereDate('date', $date)
            ->first();

        // если у пути есть массив координат, то прекращаем работу, дополнение координатами
        // новыми точками для данного маршрута происходит в другом месте
        if(!empty($route->data))  return self::SUCCESS;

        // если его нет создаем пустой без координат
        if(!$route){
            $route = $this->createEmptyRoute($device, $date);
        }

        $track['route_id'] = $route->id;

        // получаем преобразованные координаты
        $coords = $this->service->getCoordsForRoute([
            "trailer_id" => $device->trailer->id,
            "date_from" => $date,
            "date_to" => $date
        ], $device->company_id);

        // если координат нет, прекращаем работу
        if(empty($coords)) return self::SUCCESS;

        // id последней точки координат, пишем в роут, чтоб при добавлении новых
        // координат отталкиваться от этой точки
        $lastPointId = last(last($coords))['id'] ?? null;
        $track['last_point_id'] = $lastPointId;

        $command = resolve(GetRouteCommand::class);
        try {
            // тут процесс запросов к гуглу, для получения нормализованных (привязанных к дороге) координат
            $this->processRequest($route, $command, $coords);
            $route->update(['last_point_id' => $lastPointId]);
        } catch (\Throwable $e){
            Telegram::error('GoogleRoadApiWorker', authUser()->email ?? null, [
                'msg' => $e->getMessage()
            ]);
            throw new \Exception($e);
        }

        $track['google_api_request'] = $this->reqCount;
        Telegram::info('GoogleRoadApiWorker', authUser()->email ?? null, $track);
    }

    private function processRequest (
        Route $route,
        RequestCommand $command,
        array $coords
    )
    {
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
