<?php

namespace App\Services\DeliveryServices\Handlers;

use App\Clients\Fedex\FedexHttpClient;
use App\Clients\Fedex\FedexHttpRequest;
use App\Dto\Delivery\DeliveryAddressRateDto;
use App\Dto\Delivery\DeliveryRateDto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;

class FedexRateHandler extends AbstractDeliveryDriverRateHandler
{
    /**
     * @throws Exception
     */
    public function mapToStructure(Response $response): array
    {
        $data = [];

        if ($response->json('output.rateReplyDetails') === null) {
            throw new Exception(json_encode($response->json()));
        }

        foreach ($response->json('output.rateReplyDetails') as $item) {
            $id = $item['serviceDescription']['serviceId'];
            if(in_array($id, config('services.fedex.leave_only_code'))) {
                $data[] = DeliveryRateDto::byArgs([
                    'id' => $id,
                    'name' => $item['serviceName'],
                    'amount' => $item['ratedShipmentDetails'][0]['totalNetCharge'],
                    'date' => Carbon::parse($item['operationalDetail']['deliveryDate']),
                    'text_additional' => $item['commit']['transitDays']['description'],
                ]);
            }
        }
        return $data;
    }
    public function execute(DeliveryAddressRateDto $dto): Response
    {
        $usps = new FedexHttpClient();
        $request = new FedexHttpRequest('/rate/v1/rates/quotes', 'post');
        $inventories = $dto->getInventories();

        $body = [
            'accountNumber' => [
                'value' => config('services.fedex.client_rate_account')
            ],
            'rateRequestControlParameters' => [
                'returnTransitTimes' => true
            ],
            'carrierCodes' => ['FDXG', 'FDXE'],
            'requestedShipment' => [
                /*'smartPostInfoDetail' => [
                    'ancillaryEndorsement' => 'ADDRESS_CORRECTION',
                    'hubId' => 5602,
                ],*/
                'shipper' => [
                    'address' => [
                        'streetLines' => ['1529 N 31St Ave'],
                        'city' => 'Melrose Park',
                        'postalCode' => '60160',
                        'stateOrProvinceCode' => 'IL',
                        'countryCode' => 'US'
                    ]
                ],
                'recipient' => [
                    'address' => [
                        'streetLines' => [$dto->getAddress()],
                        'city' => $dto->getCity(),
                        'postalCode' => $dto->getZip(),
                        'stateOrProvinceCode' => $dto->getState(),
                        'countryCode' => 'US'
                    ]
                ],
                'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
                'shipDateStamp' => now()->addDay()->format('Y-m-d'),
                'rateRequestType' => ['ACCOUNT'],
                'groundShipment' => true,
            ]
        ];

        foreach ($inventories as $item) {
            $inventory = $item['inventory'];
            $body['requestedShipment']['requestedPackageLineItems'][] = [
                'groupPackageCount' => $item['count'],
                'weight' => [
                    'units' => 'LB',
                    'value' => $inventory->weight
                ],
                'dimension' => [
                    'length' => $inventory->length,
                    'width' => $inventory->width,
                    'height' => $inventory->height,
                    'units' => 'IN',
                ]
            ];
        }
        $request->setBody($body);
        return $usps->execute($request);
    }
    public function validate(DeliveryAddressRateDto $dto): bool
    {
       return true;
    }


}
