<?php

namespace App\Clients\Paypal;

use App\Contracts\Paypal\PaypalClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;


class PaypalClient implements PaypalClientInterface
{
    public function __construct(protected array $settings, protected ?string $token = null)
    {
        $this->token = $this->getAuthToken();
    }

    public function sendRequest(string $uri, array $data = [], string $method = 'post', array $headers = []): Response
    {
        $client = $this->getClient();

        if (!empty($headers)) {
            $client->withHeaders($headers);
        }

        if ($this->token) {
            $client->withToken($this->token);
        }

        $response = match ($method) {
            'post' => $client->post($uri, $data),
            'get' => $client->get($uri, $data),
        };

        return $this->handleResponse($response);
    }

    public function handleResponse(Response $response): Response
    {
        return $response;
    }

    protected function enableSandbox(): bool
    {
        return data_get($this->settings, 'enable_sandbox', false);
    }

    protected function getBaseUrl(): string
    {
        return $this->enableSandbox() ? static::API_BASE_URL : static::API_SANDBOX_URL;
    }

    protected function getClient(): PendingRequest
    {
        return Http::baseUrl($this->getBaseUrl());
    }

    protected function getAuthCredentials(): string
    {
        return base64_encode(
            data_get($this->settings, 'client_id') . ':' . data_get($this->settings, 'client_secret')
        );
    }

    protected function getAuthToken(): string
    {
        $response =  $this->sendRequest('v1/oauth2/token', [$this->getAuthCredentials()]);

        return $response['access_token'];
    }
}
