<?php

namespace App\Services\OneC\Commands\Order;

use App\Contracts\Utilities\Dispatchable;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Requests\RequestCommand;
use App\Models\Orders\Dealer\Order;

class CreateDealerOrder extends BaseDealerOrderCommand
{
    public function nameCommand(): string
    {
        return RequestCommand::CREATE_DEALER_ORDER;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.dealer.order.create");
    }

    protected function afterRequest(Dispatchable $model, $response): void
    {
        $err = null;
        $data['error'] = $err;

        if (data_get($response, 'error') != null
            && data_get($response, 'error') !== ""
        )
        {
            if(is_array(data_get($response, 'error')) && !empty(data_get($response, 'error'))){
                $errors = data_get($response, 'error');
                $err = implode('<br>', $errors);
            }
            $data['error'] =  $err;
        }

        if($err === null){
            $data['guid'] = data_get($response, 'guid');
        }

        if (($response['success'] ?? false) && $err === null) {
            $data['status'] =  OrderStatus::SENT;
        }

        /** @var $model Order */
        $model->update($data);
    }
}
