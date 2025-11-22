<?php

namespace App\Services\Requests\ECom\Commands\Category;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class CategoryCreateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.category.create");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Post;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Category */
        $tmp = [
            'guid' => (string)$data->id,
            'slug' => $data->slug,
            'active' => $data->active,
            'display_menu' => $data->display_menu,
            'parent_guid' => $data->parent_id ? (string)$data->parent_id : null,
            'sort' => $data->position,
            'translations' => [
                [
                    'language' => 'en',
                    'name' => $data->name,
                    'description' => $data->desc,
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
