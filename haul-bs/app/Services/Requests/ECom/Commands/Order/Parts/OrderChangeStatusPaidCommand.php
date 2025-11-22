<?php

namespace App\Services\Requests\ECom\Commands\Order\Parts;

use App\Enums\Orders\Parts\OrderPaymentStatus;
use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class OrderChangeStatusPaidCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data);

        return str_replace('{id}', $data['id'], config("requests.e_com.paths.order.parts.change_status_paid"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Order */
        if($data->isPaid()){
            $status = OrderPaymentStatus::Paid->toUpperCase();
        } else{
            $status = OrderPaymentStatus::Not_paid->toUpperCase();
        }
        if($data->isRefunded()){
            $status = OrderPaymentStatus::Refunded->toUpperCase();
        }

        $tmp = [
            'id' => $data->id,
            'status_payment' => $status
        ];

        return $tmp;
    }
}
