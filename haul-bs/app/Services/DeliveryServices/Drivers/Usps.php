<?php

namespace App\Services\DeliveryServices\Drivers;

use App\Clients\Usps\UspsHttpClient;
use App\Clients\Usps\UspsHttpRequest;
use App\Enums\Orders\Parts\DeliveryStatus;

class Usps extends AbstractDeliveryDriver
{
    public function getStatusTracking(): string
    {
        $usps = new UspsHttpClient();
        $request = new UspsHttpRequest(sprintf('/tracking/v3/tracking/%s', $this->delivery->tracking_number), 'get');

        $request->setBody([
            'expand' => 'DETAIL'
        ]);

        $response = $usps->execute($request);

        return $response->json('statusCategory');
    }

    public function mapToOrderDeliveryStatus(): DeliveryStatus
    {
        $status = $this->getStatusTracking();

        return match ($status){
            'Delivered' => DeliveryStatus::Delivered,
            default => DeliveryStatus::Sent,
        };
    }
}
