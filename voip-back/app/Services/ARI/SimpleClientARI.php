<?php

namespace App\Services\ARI;

use App\Services\ARI\Exceptions\ClientAriException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpleClientARI implements ClientARI
{


    public function __construct(
        protected string $host,
        protected string $port,
        protected string $username,
        protected string $password,
        protected array $settings,
    )
    {
//        dd(
//            $this->host,
//            $this->port,
//            $this->username,
//            $this->password,
//        );
    }

    public function get(string $uri): array
    {
        try {
            $res = $this->connection()->get($uri);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);
                throw new \Exception(data_get($data, 'errors.0.reason'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info('[client-ari] ERROR', [$e]);
            throw new ClientAriException($e->getMessage(), $e->getCode());
        }
    }

    public function post(string $uri)
    {
        try {
            $res = $this->connection()->post($uri);

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);
                throw new \Exception(data_get($data, 'errors.0.reason'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info('[ari-client] ERROR', [$e]);
            throw new ClientAriException($e->getMessage(), $e->getCode());
        }
    }

    public function delete(string $uri)
    {
        try {
            logger_seq('SEND TO ARI', ['uri' => $uri]);

            $res = $this->connection()->delete($uri);

            logger_seq('RES FROM ARI', [$res]);

//            dd($res);

//            dd($res->failed(), $res->body(), $res->status());

            if($res->failed()){
                throw new \Exception($res->reason(), $res->status());
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info('[ari-client] ERROR', [$e]);
            throw new ClientAriException($e->getMessage(), $e->getCode());
        }
    }


    protected function connection(): PendingRequest
    {
        return Http::withOptions($this->settings)
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($this->username, $this->password)
            ->baseUrl("$this->host:$this->port");
    }
}
