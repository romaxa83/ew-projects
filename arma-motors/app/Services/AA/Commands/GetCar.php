<?php

namespace App\Services\AA\Commands;

use App\Events\User\SendCarDataToAA;
use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Models\User\Car;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use App\Services\User\UserService;
use Illuminate\Support\Arr;

class GetCar
{
    private string $path;

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected UserService $userService,
        protected CarService $carService,
    )
    {
        $this->path = config("aa.request.get_car.path");
    }

    public function handler(Car $car)
    {
        $car->load(['user']);
        $this->path .= "vin={$car->vin}";
        if($car->number){
            $this->path .= "&number={$car->number}";
        }

        try {
            $res = $this->client->getRequest($this->path);

            AALogger::info("COMMAND GET CAR [REQUEST] , path {$this->path}");

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_GET_CAR, $car->user);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", $car->user->name);

            AALogger::info("COMMAND GET CAR [RESPONSE]", $res);

            $this->carService->completeFromAA($car, data_get($res, 'data'));
            return $car;
        }
        catch (AARequestException $e) {
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_GET_CAR, $car->user, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, $car->user->name);
            AALogger::info('COMMAND GET CAR [RESPONSE] - ERROR', json_to_array($e->getMessage()));

            event(new SendCarDataToAA($car));
        }
        catch (\Throwable $e){
//            TelegramDev::error(__FILE__, $e, $car->user->name);
            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }
}
