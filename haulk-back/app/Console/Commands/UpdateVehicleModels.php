<?php

namespace App\Console\Commands;

use App\Models\VehicleDB\VehicleMake;
use App\Models\VehicleDB\VehicleModel;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateVehicleModels extends Command
{

     protected $signature = 'vehicledb:models';

     protected $description = 'Update vehicle models';

    /**
     * @throws GuzzleException
     * @throws \Throwable
     */
    public function handle()
    {
        $this->info('Starting..');

        $make = VehicleMake::query()->where('last_updated', '<', now()->subSeconds(VehicleMake::UPDATE_INTERVAL)->timestamp)->oldest('id')->first();

        if (!$make) {
            $this->info('Make not found..');
            return;
        }

        $client = new GuzzleClient();

        try {
            $res = $client->request('GET', sprintf(VehicleModel::IMPORT_URL, $make->id));
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        if ($res->getStatusCode() == 200) {
            $this->info('Models data received, processing..');

            try {
                $decoded = json_decode($res->getBody(), true);
                $models = collect($decoded['Results']);

                /** @var Collection $chunk */
                foreach ($models->chunk(100) as $chunk) {
                    $chunk->transform(function ($item, $key) {
                        return [
                            'id' => $item['Model_ID'],
                            'make_id' => $item['Make_ID'],
                            'name' => $item['Model_Name'],
                        ];
                    });

                    VehicleModel::query()->upsert($chunk->toArray(), 'id');
                }

                $make->last_updated = now()->timestamp;
                $make->saveOrFail();

                $this->info('Done. ' . $models->count() . ' model(s) imported/updated for Make ' . $make->name . '.');
            } catch (Exception $e) {
                Log::error($e);
            }
        } else {
            $this->error('Error retreiving models data. Response status code: ' . $res->getStatusCode());
        }
    }
}
