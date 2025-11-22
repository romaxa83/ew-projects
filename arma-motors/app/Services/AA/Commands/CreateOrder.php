<?php

namespace App\Services\AA\Commands;

use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Models\Catalogs\Service\Service;
use App\Models\Order\Order;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use Illuminate\Support\Arr;

class CreateOrder
{
    private string $path;
    private bool $test;

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected OrderService $orderService
    )
    {
        $this->path = config("aa.request.create_order.path");
        $this->test = config("aa.request.create_order.test");
    }

    public function handler(Order $order): void
    {
        $order->load([
            'user',
            'service',
            'additions.car',
            'additions.dealership',
            'additions.recommendation',
        ]);

        try {
            $data = $this->generateData($order);

            AALogger::info('SEND ORDER DATA TO AA SYSTEM', $data);

            $res = $this->client->postRequest($this->path, $data);
            AALogger::info('RESPONSE from aa system', $res);

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_CREATE_ORDER, $order->user);

            $this->orderService->completeFromAA($order, Arr::get($res, 'data'));
        }
        catch (AARequestException $e) {
            AALogger::error("SEND to aa has error [{$e->getMessage()}]");
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_CREATE_ORDER, $order->user, AAResponse::STATUS_ERROR);
        }
        catch (\Throwable $e) {
            AALogger::error("SEND to aa has error [{$e->getMessage()}]", [$e]);
            throw new AARequestException($e->getMessage(), $e->getCode(), TelegramDev::LEVEL_IMPORTANT);
        }
    }

    public function generateData(Order $model): array
    {
        if($this->test){
            return $this->testData();
        }

        $startDate = null != $model->additions?->on_date
            ? $model->additions?->on_date->timestamp
            : null;

        $endDate = null != $model->additions->on_date
            ? $model->additions->on_date->addMinutes(
                $model->service->time_step ?: Service::DEFAULT_TIME_STEP
            )->timestamp
            : null;

        $comment = $model->communication;
        if($model->additions->comment) {
            $comment .= ' ,' . $model->additions->comment;
        }

        return [
            'data' => [
                'id' => '',
                'client' => isset($model->user->uuid)
                    ? $model->user->uuid->getValue()
                    : null,
                'auto' => isset($model->additions->car->uuid)
                    ? $model->additions->car->uuid->getValue()
                    : null,
                'type' => $model->service->alias,
                'base' => $model->additions?->dealership->alias,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'comment' => $comment,
                'idRecommendation' => isset($model->additions->recommendation->uuid)
                        ? $model->additions->recommendation->uuid->getValue()
                        : null,
                'workshop' => isset($model->additions->post->uuid)
                        ? $model->additions->post->uuid
                        : null,
                'mileage' => $model->additions?->mileage ?? 0,
                'planning' => [
                    [
                        "startDate" => $startDate,
                        'endDate' => $endDate,
                        'workshop' => isset($model->additions->post->uuid)
                            ? $model->additions->post->uuid
                            : null
                    ]
                ]
            ]
        ];
    }

    public function testData(): array
    {
        return [
            "data" => [
                "id" => "",
                "client" => "4e5d19f0-fc22-11eb-8274-4cd98fc26f15",
                "auto" => "70b83aa0-3e28-11ec-8277-4cd98fc26f14",
                "type" => "to",
                "base" => "arma-motors-renault",
                "startDate" => 1630530000,
                "endDate" => 1630530000,
                "idRecommendation" => null,
                "comment" => "telegram",
                "workshop" => "3c13fafb-79d6-11ec-8277-4cd98fc26f14",
                "planning" => [
                    [
                        "startDate" => 1630530000,
                        "endDate" => 1630530000,
                        "workshop" => "3c13fafb-79d6-11ec-8277-4cd98fc26f14"
                    ]
                ]
            ]
        ];
    }
}
