<?php

namespace App\Services\SendPulse\Commands;

use App\Models\Saas\Company\Company;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use App\Services\SendPulse\Exceptions\SendPulseApiExceptions;
use App\Services\SendPulse\Exceptions\SendPulseUnauthorizedExceptions;
use App\Services\SendPulse\SendPulseApiClient;
use Throwable;


class SendAddressCommand implements RequestCommand
{
    const URI = 'addressbooks/{id}/emails';

    protected SendPulseApiClient $client;

    public function __construct(SendPulseApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        $uri = str_replace('{id}', config('sendpulse.address_book_id'), self::URI);
        if(array_key_exists('companies', $data)){
            $data = $this->prepareDataCompanies($data['companies']);
        }

        try {
            $res = $this->client->post($uri, $data);

            return $res;
        }
        catch (SendPulseApiExceptions $e){
            throw new SendPulseApiExceptions($e->getMessage(), $e->getCode());
        }
        catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }

    private function prepareDataCompanies($data): array
    {
        $tmp = [];
        foreach ($data as $company) {
            /** @var $company Company */
            $tmp['emails'][] = [
                'email' => $company['email'],
                'variables' => [
                    'Phone' => $company['phone'],
                    'name' => $company->getSuperAdmin()->full_name,
                ],
            ];
        }

        return $tmp;
    }

}

