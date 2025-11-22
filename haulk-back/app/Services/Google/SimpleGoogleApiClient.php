<?php

namespace App\Services\Google;

use App\Services\Saas\GPS\Flespi\Exceptions\FlespiException;
use Google\Cloud\Core\Exception\GoogleException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpleGoogleApiClient implements GoogleApiClient
{
    protected string $host;
    protected string $token;
    protected array $settings = [];

    public function __construct(
        string $host,
        string $key
    )
    {
        $this->key = $key;
        $this->host = $host;
    }

    public function get(string $uri, string $points): array
    {
        try {
            $res = $this->connection()->get($uri, [
                'path' => $points,
                'key' => $this->key,
                'interpolate' => true
            ]);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);

                logger_info('GOOGLE API ROAD FAIL',[
                    'key' => $this->key,
                    'uri' => $uri,
                    'response' => $data
                ]);
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
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->host);
    }
}

