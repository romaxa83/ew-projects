<?php

declare(strict_types=1);

namespace App\Services\DeliveryServices;

use App\Dto\Delivery\DeliveryAddressRateDto;
use App\Services\DeliveryServices\Handlers\DeliveryDriverRateHandler;
use App\Services\DeliveryServices\Handlers\FedexRateHandler;
use App\Services\DeliveryServices\Handlers\UpsRateHandler;
use Illuminate\Support\Collection;

class DeliveryRateGenerator
{
    public function generate(DeliveryAddressRateDto $dto): Collection
    {
        $handlers = [
            FedexRateHandler::class,
            UpsRateHandler::class,
        ];
        $results = collect();

        foreach ($handlers as $handlerClass) {
            /** @var DeliveryDriverRateHandler $handler */
            $handler = app($handlerClass);
            $results = $results->merge($handler->handle($dto));
        }

        return $results;
    }
}
