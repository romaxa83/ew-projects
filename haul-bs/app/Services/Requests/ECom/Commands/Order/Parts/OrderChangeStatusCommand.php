<?php

namespace App\Services\Requests\ECom\Commands\Order\Parts;

use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class OrderChangeStatusCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'id');

        return str_replace('{id}', $data['id'], config("requests.e_com.paths.order.parts.change_status"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Order */

        $tmp = [
            'id' => $data->id,
            'status' => $data->status->toUpperCase(),
        ];

        if($data->status->isSent()){
            foreach ($data->deliveries as $delivery){
                $tmp['deliveries'][] = [
                    'guid' => (string)$delivery->id,
                    'tracking_number' => $delivery->tracking_number,
                    'method' => $delivery->method->toUpperCase(),
                    'status' => $delivery->status->toUpperCase(),
                ];
            }
        }

        return $tmp;
    }
}

