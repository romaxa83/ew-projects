<?php

namespace App\Services\Requests\ECom\Commands\Category;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class CategoryUpdateImagesCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'guid');

        return str_replace('{id}', $data['guid'], config("requests.e_com.paths.category.update_images"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Category */
        $data->refresh();
        $tmp = [
            'guid' => (string)$data->id,
        ];

        if($image = $data->seo?->getFirstImage()){
            /** @var $imageService ImageResourceTransformer */
            $imageService = resolve(ImageResourceTransformer::class);

            $tmp['seoImage'] = $imageService->toRequest($image);
        }
        if($imageHeader = $data->getHeaderImg()){
            /** @var $imageService ImageResourceTransformer */
            $imageService = resolve(ImageResourceTransformer::class);

            $tmp['headerImage'] = $imageService->toRequest($imageHeader);
        }
        if($imageMenu = $data->getMenuImg()){
            /** @var $imageService ImageResourceTransformer */
            $imageService = resolve(ImageResourceTransformer::class);

            $tmp['menuImage'] = $imageService->toRequest($imageMenu);
        }
        if($mobileMenu = $data->getMobileImg()){
            /** @var $imageService ImageResourceTransformer */
            $imageService = resolve(ImageResourceTransformer::class);

            $tmp['mobileImage'] = $imageService->toRequest($mobileMenu);
        }

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
