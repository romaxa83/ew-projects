<?php

namespace App\Repositories\Usdot;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Log;
use Throwable;

class UsdotApiRepository implements UsdotRepository
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;

        $this->init();
    }

    private function init(): void
    {
        $this->client->acceptJson();
    }

    public function fetchCarrierByUsdot(int $usdot): ?array
    {
        try {
            return $this->get(sprintf('carriers/%s', $usdot));
        } catch (Throwable $ex) {
            Log::error($ex);

            return null;
        }
    }

    private function get(string $path, array $query = []): array
    {
        $query = array_merge($query, ['webKey' => $this->getApiKey()]);

        return $this->usdot()
            ->get($path, $query)
            ->json();
    }

    private function getApiKey(): string
    {
        return config('usdot.api_key');
    }

    private function usdot(): PendingRequest
    {
        return clone $this->client->baseUrl(config('usdot.url'));
    }

    public function fetchAuthorityByUsdot(int $usdot): ?array
    {
        try {
            return $this->get(sprintf('carriers/%s/authority', $usdot));
        } catch (Throwable $ex) {
            Log::error($ex);

            return null;
        }
    }

    public function fetchDocketNumbersByUsdot(int $usdot): ?array
    {
        try {
            return $this->get(sprintf('carriers/%s/docket-numbers', $usdot));
        } catch (Throwable $ex) {
            return null;
        }
    }
}
