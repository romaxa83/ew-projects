<?php

namespace App\Services\Saas\GPS\Flespi;

use App\Services\Saas\GPS\Flespi\Exceptions\FlespiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpleFlespiClient implements FlespiClient
{
    protected string $host;
    protected string $token;
    protected array $settings;

    public function __construct(
        string $host,
        string $token,
        array $settings
    )
    {
        $this->host = $host;
        $this->token = $token;
        $this->settings = $settings;
    }

    public function get(string $uri, array $query = [], bool $ignoreException = false): array
    {
        try {
            $res = $this->connection()->get($uri, $query);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);
                throw new \Exception(data_get($data, 'errors.0.reason'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){

            if($ignoreException){
                return [];
            }
            logger_flespi($e->getMessage(), ['exception' => $e]);

            throw new FlespiException($e->getMessage(), $e->getCode());
        }
    }

    public function post(string $uri, array $data): array
    {
        try {
            $res = $this->connection()->post($uri, $data);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);

                $msg = 'Response from flespi service - ';
                if($data){
                    $msg .= data_get($data, 'errors.0.reason');
                } else {
                    $msg .= $res->body();
                }

                throw new \Exception($msg, $res->status());
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_flespi($e->getMessage(), ['exception' => $e]);
            throw new FlespiException($e->getMessage(), $e->getCode());
        }
    }


    protected function connection(): PendingRequest
    {
        return Http::withOptions(
            $this->settings
        )
            ->acceptJson()
            ->asJson()
            ->withToken($this->token, 'FlespiToken')
            ->baseUrl($this->host);
    }
}
