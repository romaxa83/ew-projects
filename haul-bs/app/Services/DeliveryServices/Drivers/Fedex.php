<?php

namespace App\Services\DeliveryServices\Drivers;

use App\Clients\Fedex\FedexHttpClient;
use App\Clients\Fedex\FedexHttpRequest;
use App\Enums\Orders\Parts\DeliveryStatus;

class Fedex extends AbstractDeliveryDriver
{
    public function getStatusTracking(): string
    {
        $usps = new FedexHttpClient(true);
        $request = new FedexHttpRequest(sprintf('/track/v1/trackingnumbers'), 'post');
        $request->setBody([
            'includeDetailedScans' => true,
            'trackingInfo' => [
               [
                   'trackingNumberInfo' => [
                       'trackingNumber' => $this->delivery->tracking_number
                   ]
               ]
            ]
        ]);
        $response = $usps->execute($request);

        return $response->json('output.completeTrackResults.0.trackResults.0.latestStatusDetail.statusByLocale');
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
