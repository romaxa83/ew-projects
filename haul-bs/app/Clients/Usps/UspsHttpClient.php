<?php

namespace App\Clients\Usps;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UspsHttpClient
{
    protected string $baseUrl;
    protected ?string $token = null;
    protected bool $withLogging = false;

    public function __construct()
    {
        $config = config('services.usps');

        $baseUrl = Arr::get($config, 'url');
        $clientId = Arr::get($config, 'client_key');
        $clientToken = Arr::get($config, 'client_secret');

        $withLogging = Arr::get($config, 'logging', false);

        if (empty($baseUrl) || empty($clientId) || !isset($clientToken)) {
            throw new Exception('Not set credentials for USPS integration');
        }

        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL');
        }

        $baseUrl = parse_url($baseUrl);
        $this->baseUrl = $baseUrl['scheme'] . '://' . $baseUrl['host'];

        $this->token = Cache::remember('usps_clientId', 100, function () use($clientId, $clientToken) {
            $request = (new UspsHttpRequest('/oauth2/v3/token', 'post'));
            $request->setBody([
                'client_id' => $clientId,
                'client_secret' => $clientToken,
                'grant_type' => 'client_credentials',
                'scope' => 'tracking',
            ]);

            $result = $this->execute($request);

            return $result->json('access_token');
        });
        $this->withLogging = $withLogging;
    }

    public function execute(UspsHttpRequest $request): Response
    {
        $this->logRequest(__METHOD__, $request);

        $response = $this->sendRequest($request);

        $this->logResponse(__METHOD__, $response);

        return $response;
    }

    protected function sendRequest(UspsHttpRequest $request): Response
    {
        $options = [];
        $method = Str::lower($request->getMethod());

        if (!empty($request->getBody())) {
            if ($method === 'get') {
                $options = ['query' => $request->getBody()];
            } else {
                $options = ['json' => $request->getBody()];
            }
        }

        return Http::withHeaders(
            $this->token ?
                array_merge([
                    'Authorization' => 'Bearer ' . $this->token],
                    $request->getHeaders()
                ) : $request->getHeaders()
        )->send($method, $this->baseUrl . $request->getPath(), $options);
    }

    protected function logRequest(string $method, UspsHttpRequest $request): void
    {
        if ($this->isWithLogging()) {
            Log::debug(
                "USPS Http client ($method request): ",
                [
                    'path' => $request->getPath(),
                    'headers' => $request->getHeaders(),
                    'body' => $request->getBody(),
                ]
            );
        }
    }

    protected function logResponse(string $method, Response $response): void
    {
        $context = [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
        ];

        if (!($response->ok() || $response->created())) {
            Log::error("USPS Http client ($method response): ", $context);
        } elseif ($this->isWithLogging()) {
            Log::debug("USPS Http client ($method response): ", $context);
        }
    }

    protected function isWithLogging(): bool
    {
        return $this->withLogging;
    }
}
