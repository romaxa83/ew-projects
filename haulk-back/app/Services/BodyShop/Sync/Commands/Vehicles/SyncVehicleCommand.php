<?php

namespace App\Services\BodyShop\Sync\Commands\Vehicles;

use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\BodyShop\Sync\BSApiClient;
use App\Services\Google\Commands\RequestCommand;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use Spatie\MediaLibrary\Models\Media;
use Throwable;

class SyncVehicleCommand implements RequestCommand
{
    const URI = '/api/v1/webhooks/vehicles/sync';

    protected BSApiClient $client;

    public function __construct(BSApiClient $client)
    {
        $this->client = $client;
    }

    public function handler(array $data = []): array
    {
        logger_info('SYNC VEHICLE', [$data]);
        try {
            $res = $this->client->post(self::URI, $data);

            return $res;
        } catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }

    public function fill($model)
    {
        $mediaData = [];
        $tagData = [];
        foreach ($model->media as $media){
            /** @var $media Media */
            if($media->collection_name == 'attachments'){
                $mediaData[] = [
                    'id' => $media->id,
                    'model_type' => $media->model_type,
                    'model_id' => $media->model_id,
                    'collection_name' => $media->collection_name,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'disk' => $media->disk,
                    'size' => $media->size,
                    'manipulations' => $media->manipulations,
                    'custom_properties' => $media->custom_properties,
                    'responsive_images' => $media->responsive_images,
                    'order_column' => $media->order_column,
                ];
            }
        }
        foreach ($model->tags as $tag){
            /** @var $tag Tag */
            if($tag->type == Tag::TYPE_TRUCKS_AND_TRAILER){
                $tagData[] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                ];
            }
        }

        /** @var $model Truck|Trailer */
        return [
            'vehicle_type' => $model instanceof Truck
                ? 'truck'
                : 'trailer',
            'id' => $model->id,
            'vin' => $model->vin,
            'unit_number' => $model->unit_number,
            'make' => $model->make,
            'model' => $model->model,
            'year' => $model->year,
            'color' => $model->color,
            'gvwr' => $model->gvwr,
            'type' => $model instanceof Truck
                ? $model->type
                : null,
            'license_plate' => $model->license_plate,
            'temporary_plate' => $model->temporary_plate,
            'notes' => $model->notes,
            'created_at' => $model->created_at->timestamp,
            'company' => [
                'id' => $model->getCompanyId(),
                'name' => $model->getCompany()->name
            ],
            'customer' => [
                'id' => $model->owner->id,
                'first_name' => $model->owner->first_name,
                'last_name' => $model->owner->last_name,
                'phone' => $model->owner->phone,
                'phone_extension' => $model->owner->phone_extension,
                'phones' => $model->owner->phones,
                'email' => $model->owner->email
            ],
            'media' => $mediaData,
            'tags' => $tagData
        ];
    }
}

