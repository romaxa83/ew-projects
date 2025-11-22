<?php

namespace App\Services\Requests\ECom;

use App\Services\Requests\Exceptions\RequestException;
use App\Services\Requests\RequestClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class EComRequestClient implements RequestClient
{
    public function __construct(
        protected string $host,
        protected string $token,
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
            $res = $this->connection()->get($uri, $query);

            $this->hasError($res);

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
        try {
            $res = $this->connection($headers)->post($uri, $data);

            $this->hasError($res);

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new RequestException($e->getMessage(), $e->getCode());
        }
    }

    public function put(
        string $uri,
        array $data = [],
        array $headers = []
    ): array
    {
        try {
            $res = $this->connection($headers)->put($uri, $data);

            $this->hasError($res);

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new RequestException($e->getMessage(), $e->getCode());
        }
    }

    public function putAsync(
        string $uri,
        array $data = [],
        array $headers = []
    ): array
    {
        try {
            $res = $this->connection($headers)->async()->put($uri, $data);

            $this->hasError($res);

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new RequestException($e->getMessage(), $e->getCode());
        }
    }

    public function delete(
        string $uri,
        array $headers = []
    ): array
    {
        try {
            $res = $this->connection($headers)->delete($uri);

            $this->hasError($res);

            if($res->body()){
                return json_decode($res->body(), true, 512);
            }

            return [];
        } catch (\Throwable $e){
            logger_info($e);
            throw new RequestException($e->getMessage(), $e->getCode());
        }
    }

    protected function connection(array $headers = []): PendingRequest
    {
        return Http::withOptions(
            $this->settings
        )
            ->withHeaders($headers)
            ->acceptJson()
            ->asJson()
            ->withToken($this->token)
            ->baseUrl($this->host);
    }

    protected function hasError(Response $response): void
    {
        if($response->failed()){
            $error = json_decode($response->body(), true, 512);
            $msg = 'Something wrong';
            if(isset($error['message'])) $msg = $error['message'];

            throw new RequestException($msg, $response->status());
        }
    }
}
