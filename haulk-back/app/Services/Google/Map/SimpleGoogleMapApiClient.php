<?php

namespace App\Services\Google\Map;

use Google\Cloud\Core\Exception\GoogleException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpleGoogleMapApiClient implements GoogleMapApiClient
{
    protected string $host;
    protected string $token;
    protected array $settings = [];

    public function __construct(
        string $host,
        string $token
    )
    {
        $this->token = $token;
        $this->host = $host;
    }
//https://maps.googleapis.com/maps/api/directions/json?origin=АДРЕС1&destination=АДРЕС2&units=imperial&key=ВАШ_КЛЮЧ_API
    public function get(string $uri, array $params = []): array
    {
        try {
//            dd($params);
            $res = $this->connection()->get($uri, [
                'origin' => $params['origin'],
                'destination' => $params['destination'],
                'units' => 'imperial',
                'key' => $this->token,
            ]);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);

                logger_info('GOOGLE API MAP FAIL',[
                    'origin' => $params['origin'],
                    'destination' => $params['destination'],
                    'units' => 'imperial',
                    'key' => $this->token,
                    'uri' => $uri,
                ]);
                throw new \Exception(data_get($data, 'error.message'),data_get($data, 'error.code'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new GoogleException($e->getMessage(), $e->getCode());
        }
    }

    protected function connection(): PendingRequest
    {
        return Http::withOptions(
            $this->settings
        )
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->host);
    }
}


