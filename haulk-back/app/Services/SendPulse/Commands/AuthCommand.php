<?php

namespace App\Services\SendPulse\Commands;

use App\Models\SendPulse\AuthToken;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use App\Services\SendPulse\Exceptions\SendPulseApiExceptions;
use App\Services\SendPulse\SendPulseApiClient;
use Throwable;


class AuthCommand implements RequestCommand
{
    const URI = 'oauth/access_token';
    const TYPE = 'auth';

    protected SendPulseApiClient $client;

    public function __construct(SendPulseApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        try {
            $res = $this->client->post(self::URI, [self::TYPE]);

            AuthToken::updateOrCreate([
                'token_type' => $res['token_type']
            ], $res);

            return $res;
        }
        catch (SendPulseApiExceptions $e){
            throw new SendPulseApiExceptions($e->getMessage(), $e->getCode());
        }
        catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }
}
