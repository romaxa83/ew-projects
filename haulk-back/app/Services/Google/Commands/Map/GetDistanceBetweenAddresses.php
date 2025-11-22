<?php

namespace App\Services\Google\Commands\Map;

use App\Services\Google\Commands\RequestCommand;
use App\Services\Google\Map\GoogleMapApiClient;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use Google\Cloud\Core\Exception\GoogleException;
use Throwable;

//https://maps.googleapis.com/maps/api/directions/json?origin=АДРЕС1&destination=АДРЕС2&units=imperial&key=ВАШ_КЛЮЧ_API

class GetDistanceBetweenAddresses implements RequestCommand
{
    const URI = 'maps/api/directions/json';

    protected GoogleMapApiClient $client;

    public function __construct(GoogleMapApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        try {
            logger_info('GetDistanceBetweenAddresses START', $data);

            $res = $this->client->get(self::URI, $data);

            logger_info('GetDistanceBetweenAddresses RES', [$res]);

            return $this->normalizeData($res);
        }
        catch (GoogleException $e){
            throw new GoogleException($e->getMessage(), $e->getCode());
        }
        catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }

    private function normalizeData(array $res): array
    {
        return [
            'distance' => $res['routes'][0]['legs'][0]['distance']['value']
                ? convert_meters_to_miles($res['routes'][0]['legs'][0]['distance']['value'])
                : null,
            'distance_text' => $res['routes'][0]['legs'][0]['distance']['text'] ?? null,
            'start' => [
                'address' => $res['routes'][0]['legs'][0]['start_address'] ?? null,
                'location' => $res['routes'][0]['legs'][0]['start_location'] ?? null
            ],
            'end' => [
                'address' => $res['routes'][0]['legs'][0]['end_address'] ?? null,
                'location' => $res['routes'][0]['legs'][0]['end_location'] ?? null
            ]
        ];
    }
}



