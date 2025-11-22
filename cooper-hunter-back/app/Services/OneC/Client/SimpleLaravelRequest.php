<?php

namespace App\Services\OneC\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class SimpleLaravelRequest implements RequestClient
{
    public string $baseUrl;

    public function __construct(private array $config)
    {
        $this->baseUrl = data_get($this->config, 'base_url') . '/' . data_get($this->config, 'base_url_suffix');
    }

    public function getRequest(string $uri): array
    {
        return [];
    }

    public function postRequest(string $uri, array $data = []): array
    {
        try {
            $response = $this->getConnection()->post($uri, $data);

            $data = json_decode($response->body(), true, 512);

            if(null == $data && is_string($response->body())){
                $msg = $response->body();
                if($response->body() == ''){
                    $msg = json_encode($response->handlerStats(), true, 512);
                }

                throw new \Exception($msg, 500);
            }

            return $data;
        } catch (\Throwable $e){
            logger($e);
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    protected function getConnection(): PendingRequest
    {

        return Http::withOptions(
            [
                'timeout' => data_get($this->config, 'timeout'),
                'connect_timeout' => data_get($this->config, 'connection_timeout'),

            ]
        )
            ->acceptJson()
            ->asJson()
            ->withBasicAuth(
                data_get($this->config, 'login'),
                data_get($this->config, 'password')
            )
            ->baseUrl($this->baseUrl);
    }
}


