<?php

namespace App\Services\BodyShop\Sync\Commands\Vehicles;

use App\Models\Vehicles\Truck;
use App\Services\BodyShop\Sync\BSApiClient;
use App\Services\Google\Commands\RequestCommand;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use Throwable;

class DeleteVehicleCommand implements RequestCommand
{
    const URI = '/api/v1/webhooks/vehicles/{type}/{id}';

    protected BSApiClient $client;

    public function __construct(BSApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        try {
            if($vehicle = $data['vehicle'] ?? null){
                $type = $vehicle instanceof Truck ? 'truck' : 'trailer';
                $uri = str_replace('{type}', $type, self::URI);
                $uri = str_replace('{id}', $vehicle->id, $uri);

                $res = $this->client->delete($uri);

                return $res;
            }

        } catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }

        return [];
    }
}
