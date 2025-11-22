<?php

namespace App\Services\Requests\ECom\Commands\Inventory;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class InventoryUpdateImagesCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'guid');

        return str_replace('{id}', $data['guid'], config("requests.e_com.paths.inventory.update_images"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Inventory */
        $data->refresh();
        $tmp = [
            'guid' => (string)$data->id,
        ];

        /** @var $imageService ImageResourceTransformer */
        $imageService = resolve(ImageResourceTransformer::class);

        if($mainImg = $data->getMainImg()){
            $tmp['mainImage'] = $imageService->toRequest($mainImg);
        }
        foreach ($data->getGallery() ?? [] as $img){
            $tmp['gallery'][] = $imageService->toRequest($img);
        }

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
