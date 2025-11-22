<?php

namespace App\Console\Commands;

use App\Models\VehicleDB\VehicleMake;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateVehicleMakes extends Command
{

    protected $signature = 'vehicledb:makes';

    protected $description = 'Update vehicle makes';

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        $this->info('Starting..');

        $client = new GuzzleClient();

        try {
            $res = $client->request('GET', VehicleMake::IMPORT_URL);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        if ($res->getStatusCode() == 200) {
            $this->info('Makes data received, processing..');

            try {
                $decoded = json_decode($res->getBody(), true);
                $makes = collect($decoded['Results']);

                foreach ($makes->chunk(100) as $chunk) {
                    /** @var Collection $chunk */
                    $chunk->transform(function ($item, $key) {
                        return [
                            'id' => $item['Make_ID'],
                            'name' => $item['Make_Name'],
                        ];
                    });

                    VehicleMake::query()->upsert($chunk->toArray(), 'id');
                }

                $this->info('Done. ' . $makes->count() . ' makes imported/updated.');
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            $this->error('Error retreiving makes data. Response status code: ' . $res->getStatusCode());
        }
    }
}
