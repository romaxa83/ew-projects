<?php

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

trait RequestToEComm
{
    protected function getAuthHeader(): array
    {
        return [
            'Authorization' => config('api.e_comm.token')
        ];
    }

    protected function getJsonEComm(string $uri, array $headers = []): TestResponse
    {
        return $this->getJson($uri, $this->getHeaders($headers));
    }

    protected function postJsonEComm(string $uri, array $data, array $headers = []): TestResponse
    {
        return $this->postJson($uri, $data, $this->getHeaders($headers));
    }

    protected function putJsonEComm(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->putJson($uri, $data, $this->getHeaders($headers));
    }

    protected function deleteJsonEComm(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->deleteJson($uri, $data, $this->getHeaders($headers));
    }

    private function getHeaders(array $headers = []): array
    {
        return array_merge($this->getAuthHeader(), $headers);
    }
}
