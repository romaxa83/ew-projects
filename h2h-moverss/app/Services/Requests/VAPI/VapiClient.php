<?php

namespace App\Services\Requests\VAPI;

use App\Services\Requests\Exceptions\RequestException;
use App\Services\Requests\RequestClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class VapiClient implements RequestClient
{
    public function __construct(
        protected string $url,
        protected string $token,
        protected array $settings = [],
    )
    {}

    public function get(
        string $uri,
        array $query = [],
        array $headers = []
    ): array
    {
        $res = $this->connection()->get($uri, $query);

        $this->hasError($res);

        return $this->decode($res);
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
            ->withToken($this->token)
            ->withHeaders($headers)
            ->baseUrl($this->url);
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
            $data = $this->decode($response);

            $msg = $data['message'];
            $code = $data['statusCode'];

            throw new RequestException("VAPI request FAILED. [$msg]", $code);
        }
    }

    protected function decode(Response $response): array
    {
        return json_decode($response->body(), true, 512);
    }
}
