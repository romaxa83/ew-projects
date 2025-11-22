<?php

namespace App\Services\BodyShop\Sync;

use Google\Cloud\Core\Exception\GoogleException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpleBSApiClient implements BSApiClient
{
    protected string $host;
    protected string $token;
    protected array $settings = [];

    public function __construct(
        string $host,
        string $token
    )
    {
        $this->token = $token;
        $this->host = $host;
    }

    public function post(string $uri, array $data): array
    {
        try {
//            logger_info('SimpleBSApiClient START', [$data]);
            $res = $this->connection()->post($uri, ['data' => $data]);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);
                logger_info('SimpleBSApiClient FAIL', [$data]);
                throw new \Exception(data_get($data, 'error.message'),data_get($data, 'error.code'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new GoogleException($e->getMessage(), $e->getCode());
        }
    }

    public function delete(string $uri, array $data = []): array
    {
        try {
//            logger_info('SimpleBSApiClient START', [$data]);
            $res = $this->connection()->delete($uri, $data);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);
                logger_info('SimpleBSApiClient FAIL', [$data]);
                throw new \Exception(data_get($data, 'error.message'),data_get($data, 'error.code'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new GoogleException($e->getMessage(), $e->getCode());
        }
    }

    protected function connection(): PendingRequest
    {
        return Http::withOptions(
            $this->settings
        )
            ->withHeaders([
                'Authorization' => $this->token
            ])
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->host);
    }
}
