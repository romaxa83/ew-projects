<?php

namespace App\Services\AA\Client;

use App\Services\AA\Exceptions\AARequestException;
use App\Services\Telegram\TelegramDev;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class SimpleLaravelRequest implements RequestClient
{
    public function __construct(
        private $url,
        private $token
    ){}

    private function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $this->token
        ];
    }

    public function getRequest(string $uri): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get($this->url . $uri);

            if($response->status() != Response::HTTP_OK){
                throw new AARequestException($response->body(), $response->status());
            }

            return json_to_array($response->body());
        } catch (\Throwable $e){
            TelegramDev::info('!!!!!!!!!!!!!!!!! Ошибка запроса к AA');
            logger($e->getMessage());
            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }

    public function getRequestWithoutException(string $uri): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get($this->url . $uri);

            return json_to_array($response->body());
        } catch (\Throwable $e){
            logger($e->getMessage());
        }
    }

    public function postRequest(string $uri, array $data = []): array
    {
        logger('SEND DATA TO AA', [
            'uri' => $uri,
            'data' => $data,
        ]);
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->url . $uri, $data);

            logger('RESPONSE DATA FROM AA', [$response]);

            if($response->status() != Response::HTTP_OK){
                throw new AARequestException($response->body(), $response->status());
            }

            return json_to_array($response->body());
        } catch (\Throwable $e){
            TelegramDev::info('!!!!!!!!!!!!!!!!! Ошибка запроса к AA');
            logger($e->getMessage());
            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }

    public function postRequestWithoutException(string $uri, array $data = []): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->url . $uri, $data);

            return json_to_array($response->body());
        } catch (\Throwable $e){
            logger($e->getMessage());
        }
    }
}
