<?php

namespace App\Services\OneC\Commands\Order;

use App\Contracts\Utilities\Dispatchable;
use App\Enums\Requests\RequestCommand;
use App\Models\Orders\Dealer\Order;

class UpdateDealerOrder extends BaseDealerOrderCommand
{
    public function nameCommand(): string
    {
        return RequestCommand::UPDATE_DEALER_ORDER;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.dealer.order.update");
    }

    protected function afterRequest(Dispatchable $model, $response): void
    {
        $err = null;
        if(data_get($response, 'error') != null && data_get($response, 'error') !== "") {
            $err = data_get($response, 'error');
        }

        /** @var $model Order */
        $model->update([
            'error' => $err
        ]);
    }
}
