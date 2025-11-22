<?php

namespace App\Services\SendPulse;

use App\Models\SendPulse\AuthToken;
use App\Services\SendPulse\Commands\AuthCommand;
use App\Services\SendPulse\Exceptions\SendPulseApiExceptions;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpleSendPulseApiClient implements SendPulseApiClient
{
    protected string $host;
    protected string $clientId;
    protected string $clientSecret;
    protected array $settings = [];

    public function __construct(
        string $host,
        string $clientId,
        string $clientSecret
    )
    {
        $this->host = $host;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function post(string $uri, array $data = []): array
    {
        try {
            if(in_array(AuthCommand::TYPE, $data)){
                $res = $this->connection()->post($uri, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]);
            } else {

                $res = $this->connection(AuthToken::first()->access_token ?? null)
                    ->post($uri, $data);

                if($res->status() == 401){
                    $command = resolve(AuthCommand::class);
                    $command->handler();

                    $res = $this->connection(AuthToken::first()->access_token ?? null)
                        ->post($uri, $data);
                }
            }

            if($res->failed()){
                $data = json_decode($res->body(), true, 512);

                throw new SendPulseApiExceptions(data_get($data, 'error.message'),data_get($data, 'error.code'));
            }

            return json_decode($res->body(), true, 512);
        } catch (\Throwable $e){
            logger_info($e);
            throw new SendPulseApiExceptions($e->getMessage(), $e->getCode());
        }
    }

    protected function connection($authToken = null): PendingRequest
    {
        $request = Http::withOptions(
            $this->settings
        )
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->host);

        if($authToken){
            $request->withHeaders([
                'Authorization' => 'Bearer ' . $authToken
            ]);
        }

        return $request;
    }
}
