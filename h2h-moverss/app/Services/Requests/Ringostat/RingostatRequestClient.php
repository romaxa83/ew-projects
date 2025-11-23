<?php

namespace App\Services\Requests\Ringostat;

use App\Services\Requests\Exceptions\RequestException;
use App\Services\Requests\RequestClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class RingostatRequestClient implements RequestClient
{
    public function __construct(
        protected string $host,
        protected array $settings = [],
    )
    {}

    public function get(
        string $uri,
        array $query = [],
        array $headers = []
    ): array
    {
        try {
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

    public function post(
        string $uri,
        array $data = [],
        array $headers = []
    ): array
    {
        $res = $this->connection($headers)->post($uri, $data);

        $this->hasError($res);

        return json_decode($res->body(), true, 512);
    }


    protected function connection(array $headers = []): PendingRequest
    {
        return Http::withOptions(
            $this->settings
        )
            ->acceptJson()
            ->withHeaders($headers)
            ->baseUrl($this->host);
    }



    public function put(string $uri, array $data = [], array $headers = []): array
    {
        return [];
    }

    public function delete(string $uri, array $headers = []): array
    {
        return [];
    }

    protected function hasError(Response $response): void
    {
        if($response->failed()){
            $data = json_decode($response->body(), true, 512);
            $msg = $data['message'];

            throw new RequestException("Ringostat request failed. [$msg]", $response->status());
        }
    }
}



