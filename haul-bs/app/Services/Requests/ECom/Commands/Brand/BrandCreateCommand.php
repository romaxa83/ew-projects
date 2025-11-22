<?php

namespace App\Services\Requests\ECom\Commands\Brand;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use App\Models\Inventories\Brand;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class BrandCreateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.brand.create");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Post;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Brand */
        $tmp = [
            'guid' => (string)$data->id,
            'slug' => $data->slug,
            'translations' => [
                [
                    'language' => 'en',
                    'name' => $data->name,
                    'seo_h1' => $data->seo?->h1,
                    'seo_title' => $data->seo?->title,
                    'seo_description' => $data->seo?->desc,
                    'seo_text' => $data->seo?->text,
                ]
            ]
        ];

        if($image = $data->seo?->getFirstImage()){
            /** @var $imageService ImageResourceTransformer */
            $imageService = resolve(ImageResourceTransformer::class);

            $tmp['seoImage'] = $imageService->toRequest($image);
        }

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
