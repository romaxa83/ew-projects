<?php

namespace App\Services\Requests\Google\Map;

use App\Services\Requests\Google\Exceptions\GoogleApiExceptions;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class SimpleGoogleMapApiClient implements GoogleMapApiClient
{
    protected string $host;
    protected string $token;
    protected array $settings = [];

    public function __construct(
        string $host,
        string $token
    ) {
        $this->token = $token;
        $this->host = $host;
    }

    //https://maps.googleapis.com/maps/api/directions/json?origin=АДРЕС1&destination=АДРЕС2&units=imperial&key=ВАШ_КЛЮЧ_API
    public function get(string $uri, array $params = []): array
    {
        try {
            $fullUrl = $this->buildUrl($uri, [
                'origin' => $params['origin'] ?? '',
                'destination' => $params['destination'] ?? '',
                'units' => 'imperial',
                'key' => $this->token,
            ]);

            logger_info('[request-google] GOOGLE API CLIENT MAP REQUEST', [
                'origin' => $params['origin'],
                'destination' => $params['destination'],
                'units' => 'imperial',
                'key' => $this->token,
                'full_url' => $fullUrl,
            ]);

            $res = $this->connection()->get($uri, [
                'origin' => $params['origin'],
                'destination' => $params['destination'],
                'units' => 'imperial',
                'key' => $this->token,
            ]);

            if ($res->failed()) {
                $data = json_decode($res->body(), true, 512);

                logger_info('[request-google] GOOGLE API CLIENT MAP FAIL', [
                    'origin' => $params['origin'],
                    'destination' => $params['destination'],
                    'units' => 'imperial',
                    'key' => $this->token,
                    'uri' => $uri,
                ]);

                throw new Exception(data_get($data, 'error.message'), data_get($data, 'error.code'));
            }

            return json_decode($res->body(), true, 512);
        } catch (Throwable $e) {
            logger_info($e);

            throw new GoogleApiExceptions($e->getMessage(), $e->getCode());
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

    protected function buildUrl(string $uri, array $queryParams = []): string
    {
        // Формируем базовый URL
        $url = rtrim($this->host, '/') . '/' . ltrim($uri, '/');

        // Если есть параметры, добавляем их
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }
}
