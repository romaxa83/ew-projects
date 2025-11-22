<?php

namespace App\Services\AA\Commands;

use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Models\User\Car;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use Illuminate\Support\Arr;

class CreateCar
{
    private string $path;

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected CarService $carService
    )
    {
        $this->path = config("aa.request.create_car.path");
    }

    public function handler(Car $car): void
    {
        $car->refresh();
        $car->load(['user', 'brand', 'model']);

        try {
            $data = [
                'data' => [
                    "id" => "",
                    "name" => "",
                    "brand" => $car->brand->uuid->getValue(),
                    "model" => $car->model->uuid->getValue(),
                    "year" => $car->year,
                    "yearDeal" => "",
                    "vin" => $car->vin->getValue(),
                    "number" => $car->number->getValue(),
                    "owner" => $car->user->uuid->getValue(),
                    "personal" => $car->is_personal,
                ]
            ];
            AALogger::info('COMMAND CREATE CAR [REQUEST]', $data);

            $res = $this->client->postRequest($this->path, $data);

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_CREATE_CAR, $car->user);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", $car->user->name, TelegramDev::LEVEL_IMPORTANT);

            AALogger::info('COMMAND CREATE CAR [RESPONSE]', $data);

            $this->carService->completeFromAA($car, data_get($res, 'data'));
        }
        catch (AARequestException $e) {
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_CREATE_CAR, $car->user, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, $car->user->name);
            AALogger::info('COMMAND CREATE CAR [RESPONSE] - ERROR', json_to_array($e->getMessage()));
        }
        catch (\Throwable $e) {
//            TelegramDev::error(__FILE__, $e, $car->user->name);
            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }
}





