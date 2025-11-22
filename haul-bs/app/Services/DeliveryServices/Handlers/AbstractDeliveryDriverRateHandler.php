<?php

namespace App\Services\DeliveryServices\Handlers;

use App\Dto\Delivery\DeliveryAddressRateDto;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class AbstractDeliveryDriverRateHandler implements DeliveryDriverRateHandler
{
    /**
     * @throws Exception
     */
    public function handle(DeliveryAddressRateDto $dto): array
    {
        try {
            if ($this->validate($dto)) {
                return $this->mapToStructure($this->execute($dto));
            } else {
                return [];
            }
        } catch (Exception $exception) {
            Log::debug($exception->getMessage());

            return [];
        }
    }
}
