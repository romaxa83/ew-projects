<?php

namespace App\Services\Requests\Google\Map\Commands;

use App\Services\Requests\Google\Exceptions\GoogleApiExceptions;
use App\Services\Requests\Google\Map\GoogleMapApiClient;
use App\Services\Requests\Google\RequestCommand;
use Throwable;

//https://maps.googleapis.com/maps/api/directions/json?origin=АДРЕС1&destination=АДРЕС2&units=imperial&key=ВАШ_КЛЮЧ_API

class GetDistanceBetweenAddresses implements RequestCommand
{
    public const URI = 'maps/api/directions/json';

    protected GoogleMapApiClient $client;

    public function __construct(GoogleMapApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        try {
            logger_info('[request-google] HANDLER GetDistanceBetweenAddresses START', $data);

            $res = $this->client->get(self::URI, $data);

            if (isset($res['error_message'])) {
                logger_info('[request-google] HANDLER GetDistanceBetweenAddresses RESULT FAIL', $res);

                return [];
            }

            $normalizedData = $this->normalizeData($res);

            logger_info('[request-google] HANDLER GetDistanceBetweenAddresses NORMALIZE RESULT', $normalizedData);

            return $this->normalizeData($res);
        } catch (Throwable $e) {
            throw new GoogleApiExceptions($e->getMessage(), $e->getCode());
        }
    }

    private function normalizeData(array $res): array
    {
        return [
            'distance_as_mile' => $res['routes'][0]['legs'][0]['distance']['value']
                ? convert_meters_to_miles($res['routes'][0]['legs'][0]['distance']['value'])
                : null,
            'distance_as_meters' => $res['routes'][0]['legs'][0]['distance']['value'] ?? null,
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
