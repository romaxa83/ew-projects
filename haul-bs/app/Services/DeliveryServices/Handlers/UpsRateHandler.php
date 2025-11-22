<?php

namespace App\Services\DeliveryServices\Handlers;

use App\Clients\Ups\UpsHttpClient;
use App\Clients\Ups\UpsHttpRequest;
use App\Dto\Delivery\DeliveryAddressRateDto;
use App\Dto\Delivery\DeliveryRateDto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;

class UpsRateHandler extends AbstractDeliveryDriverRateHandler
{
    /**
     * @throws Exception
     */
    public function mapToStructure(Response $response): array
    {
        $data = [];

        if ($response->json('RateResponse.RatedShipment') === null) {
            throw new Exception(json_encode($response->json()));
        }

        foreach ($response->json('RateResponse.RatedShipment') as $item) {
            $id = $item['Service']['Code'];
            if(in_array($id, config('services.ups.leave_only_code'))) {
                $dateTime = $item['TimeInTransit']['ServiceSummary']['EstimatedArrival']['Arrival']['Date'];
                $dateTime .= $item['TimeInTransit']['ServiceSummary']['EstimatedArrival']['Arrival']['Time'];
                $date = Carbon::parse($dateTime);

                $data[] = DeliveryRateDto::byArgs([
                    'id' => $id,
                    'name' => $item['TimeInTransit']['ServiceSummary']['Service']['Description'],
                    'amount' => $item['TotalCharges']['MonetaryValue'],
                    'date' => $date,
                    'text_additional' => $item['TimeInTransit']['ServiceSummary']['EstimatedArrival']['BusinessDaysInTransit'],
                ]);
            }
        }
        return $data;
    }
    public function execute(DeliveryAddressRateDto $dto): Response
    {
        $usps = new UpsHttpClient();

        $request = new UpsHttpRequest('/api/rating/v2403/Shoptimeintransit', 'post');
        $request->setHeaders([
            'transId' => now()->timestamp,
            'transactionSrc' => config('services.ups.client_key'),
        ]);
        $inventories = $dto->getInventories();

        $body = [
            'RateRequest' => [
                'Request' => [
                    'RequestOption' => 'Rate'
                ],
                'Shipment' => [
                    'Shipper' => [
                        'Address' => [
                            'AddressLine' => ['1529 N 31St Ave'],
                            'City' => 'Melrose Park',
                            'PostalCode' => '60160',
                            'StateProvinceCode' => 'IL',
                            'CountryCode' => 'US'
                        ]
                    ],
                    'ShipFrom' => [
                        'Address' => [
                            'AddressLine' => ['1529 N 31St Ave'],
                            'City' => 'Melrose Park',
                            'PostalCode' => '60160',
                            'StateProvinceCode' => 'IL',
                            'CountryCode' => 'US'
                        ],
                    ],
                    'ShipTo' => [
                        'Address' => [
                            'AddressLine' => [$dto->getAddress()],
                            'City' => $dto->getCity(),
                            'PostalCode' => $dto->getZip(),
                            'StateProvinceCode' => $dto->getState(),
                            'CountryCode' => 'US'
                        ],
                    ],
                    'DeliveryTimeInformation' => [
                        'PackageBillType' => '03',
                        'Pickup' => [
                            'Date' => now()->addDay()->format('Ymd')
                        ]
                    ]
                ]
            ],

        ];

        foreach ($inventories as $item) {
            $inventory = $item['inventory'];
            for ($i = 0; $i < $item['count']; $i++) {
                $body['RateRequest']['Shipment']['Package'][] = [
                    'PackagingType' => [
                        'Code' => '00'
                    ],
                    'Dimensions' => [
                        'UnitOfMeasurement' => [
                            'Code' => 'IN',
                            'Description' => 'Text'
                        ],
                        'Length' => (string) $inventory->length,
                        'Width' => (string) $inventory->width,
                        'Height' => (string) $inventory->height,
                    ],
                    'PackageWeight' => [
                        'UnitOfMeasurement' => [
                            'Code' => 'LBS',
                            'Description' => 'Text'
                        ],
                        'Weight' => (string) $inventory->weight,
                    ]
                ];
            }
        }

        $request->setBody($body);
        return $usps->execute($request);
    }
    public function validate(DeliveryAddressRateDto $dto): bool
    {
       return true;
    }
}
