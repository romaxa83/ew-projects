<?php

namespace App\Services\BodyShop\Sync\Commands\Vehicles;

use App\Services\BodyShop\Sync\BSApiClient;
use App\Services\Google\Commands\RequestCommand;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use Throwable;

class UnsetVehicleCommand implements RequestCommand
{
    const URI = 'api/v1/webhooks/vehicles/unset/{companyId}';

    protected BSApiClient $client;

    public function __construct(BSApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        try {
            $uri = str_replace('{companyId}', $data['company_id'], self::URI);

            $res = $this->client->post($uri, []);

            return $res;
        } catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }
}
