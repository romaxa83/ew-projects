<?php

namespace App\Services\Requests\BaseHaulk;

use App\Services\Requests\RequestClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BaseHaulkRequestClient implements RequestClient
{
    public function __construct(
        protected string $host,
        protected array $secrets,
        protected array $settings
    )
    {}

    public function get(
        string $uri,
        array $query = [],
        array $headers = []
    ): array
    {
        try {
//            dd(
//                $this->host,
//                $this->settings,
//                $this->secrets,
//            );
            $res = $this->connection()->get($uri, $query);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);

                throw new \Exception(data_get($data, 'error.message'),data_get($data, 'error.code'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }


    protected function connection(): PendingRequest
    {
        return Http::withOptions(
            $this->settings
        )
            ->acceptJson()
            ->asJson()
            ->withHeader('Authorization', data_get($this->secrets, 'token'))
            ->baseUrl($this->host);
    }

    public function post(string $uri, array $data = [], array $headers = []): array
    {
        return [];
    }

    public function put(string $uri, array $data = [], array $headers = []): array
    {
        return [];
    }

    public function putAsync(string $uri, array $data = [], array $headers = []): array
    {
        return [];
    }

    public function delete(string $uri, array $headers = []): array
    {
        return [];
    }
}


