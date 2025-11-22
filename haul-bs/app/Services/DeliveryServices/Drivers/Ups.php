<?php

namespace App\Services\DeliveryServices\Drivers;


use App\Clients\Ups\UpsHttpClient;
use App\Clients\Ups\UpsHttpRequest;
use App\Enums\Orders\Parts\DeliveryStatus;

class Ups extends AbstractDeliveryDriver
{
    public function getStatusTracking(): string
    {
        $usps = new UpsHttpClient();

        $request = new UpsHttpRequest(sprintf('/api/track/v1/details/%s', $this->delivery->tracking_number), 'get');

        $request->setHeaders([
            'transId' => $this->delivery->id,
            'transactionSrc' => 'client',
        ]);
        $response = $usps->execute($request);

        return $response->json('trackResponse.shipment.0.package.0.currentStatus.code');
    }

    public function mapToOrderDeliveryStatus(): DeliveryStatus
    {
        $status = $this->getStatusTracking();

        return match ($status){
            '040' => DeliveryStatus::Delivered,
            default => DeliveryStatus::Sent,
        };
    }
}
