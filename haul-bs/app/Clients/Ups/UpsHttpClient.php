<?php

namespace App\Clients\Ups;

use App\Clients\Fedex\FedexHttpRequest;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpsHttpClient
{
    protected string $baseUrl;
    protected ?string $token = null;
    protected bool $withLogging = false;

    public function __construct()
    {
        $config = config('services.ups');

        if (Arr::get($config, 'sandbox')) {
            $baseUrl = Arr::get($config, 'url_sandbox');
        } else {
            $baseUrl = Arr::get($config, 'url');
        }
        $clientId = Arr::get($config, 'client_key');
        $clientToken = Arr::get($config, 'client_secret');

        $withLogging = Arr::get($config, 'logging', false);

        if (empty($baseUrl) || empty($clientId) || !isset($clientToken)) {
            throw new Exception('Not set credentials for UPS integration');
        }

        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL');
        }

        $baseUrl = parse_url($baseUrl);
        $this->baseUrl = $baseUrl['scheme'] . '://' . $baseUrl['host'];

        $this->token = Cache::remember('ups_clientId', 100, function () use($clientId, $clientToken) {
            return $this->login($clientId, $clientToken);
        });

        $this->withLogging = $withLogging;
    }

    private function login($clientId, $clientToken): string
    {
        $request = (new UpsHttpRequest('/security/v1/oauth/token', 'post'));

        $request->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientToken),
            'x-merchant-id' => $clientId,
        ]);

        $request->setBody([
            'grant_type' => 'client_credentials',
        ]);

        $result = $this->execute($request, true);

        return $result->json('access_token');
    }

    public function execute(UpsHttpRequest $request, bool $form = false): Response
    {
        $this->logRequest(__METHOD__, $request);

        $response = $this->sendRequest($request, $form);

        $this->logResponse(__METHOD__, $response);

        return $response;
    }

    protected function sendRequest(UpsHttpRequest $request, bool $form = false): Response
    {
        $method = Str::lower($request->getMethod());

        $options = [];

        if (!empty($request->getBody())) {
            if ($method === 'get') {
                $options = ['query' => $request->getBody()];
            } else {
                $options = ['json' => $request->getBody()];
            }
        }

        $response = Http::withHeaders(
            $this->token ?
                array_merge([
                    'Authorization' => 'Bearer ' . $this->token],
                    $request->getHeaders()
                ) : $request->getHeaders()
        );

        if ($form) {
            $response
                ->withBody($request->getBody())
                ->asForm();
        }

        return $response->send($method, $this->baseUrl . $request->getPath(), !$form ? $options : []);
    }

    protected function logRequest(string $method, UpsHttpRequest $request): void
    {
        if ($this->isWithLogging()) {
            Log::debug(
                "UPS Http client ($method request): ",
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
            Log::error("UPS Http client ($method response): ", $context);
        } elseif ($this->isWithLogging()) {
            Log::debug("UPS Http client ($method response): ", $context);
        }
    }

    protected function isWithLogging(): bool
    {
        return $this->withLogging;
    }
}
