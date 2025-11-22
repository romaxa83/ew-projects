<?php

namespace App\Services\DeliveryServices;

use App\Clients\Fedex\FedexHttpClient;
use App\Clients\Fedex\FedexHttpRequest;
use App\Dto\Delivery\DeliveryAddressDto;
use Illuminate\Http\Client\Response;

class FedexAddressValidation
{
    public DeliveryAddressDto $dto;
    public mixed $serviceType;
    public function __construct(DeliveryAddressDto $dto)
    {
        $this->dto = $dto;
    }
    public function validate(): ?array
    {
            $response = $this->execute();

            return [
                'name' => $response->json('output.rateReplyDetails.0.serviceName'),
                'amount' => $response->json('output.rateReplyDetails.0.ratedShipmentDetails.0.totalNetCharge'),
                'date' => $response->json('output.rateReplyDetails.0.operationalDetail.deliveryDate'),
                'date_text' => $response->json('output.rateReplyDetails.0.commit.transitDays.description'),
            ];

    }

    public function execute(): Response
    {
        $usps = new FedexHttpClient();
        $request = new FedexHttpRequest('/address/v1/addresses/resolve', 'post');

        $body = [
            'validateAddressControlParameters' => [
                'includeResolutionTokens' => true
            ],
            'addressesToValidate' => [
                [
                    'address' => [
                        'streetLines' => [
                            $this->dto->getAddress()
                        ],
                        'city' => 'test',
                        'postalCode' => $this->dto->getZip(),
                        'stateOrProvinceCode' => $this->dto->getState(),
                        'countryCode' => 'US'
                    ]
                ]
            ],
        ];

        $request->setBody($body);
        return $usps->execute($request);
    }
}
