<?php

namespace App\Services\Requests\ECom\Commands\Inventory;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Requests\RequestMethodEnum;

class InventoryUpdateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'guid');

        return str_replace('{id}', $data['guid'], config("requests.e_com.paths.inventory.update"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Inventory */

        $tmp = [
            'guid' => (string)$data->id,
            'slug' => $data->slug,
            'sort' => 0,
            'active' => $data->active,
            'min_limit' => $data->min_limit,
            'discount' => $data->discount,
            'quantity' => floor($data->quantity),
            'novelty' => $data->is_new,
            'popular' => $data->is_popular,
            'sale' => $data->is_sale,
            'delivery_cost' => $data->delivery_cost,
            'cost' => $data->price_retail,
            'old_cost' => $data->old_price,
            'length' => $data->length,
            'width' => $data->width,
            'height' => $data->height,
            'weight' => $data->weight,
            'package' => $data->package_type->value,
            'for_shop' => $data->for_shop,
            'sku' => $data->stock_number,
            'article_number' => $data->article_number,
            'category_guid' => (string)$data->category_id,
            'brand_guid' => (string)$data->brand_id,
            'translations' => [
                [
                    'language' => 'en',
                    'name' => $data->name,
                    'seo_h1' => $data->seo?->h1,
                    'seo_title' => $data->seo?->title,
                    'seo_description' => $data->seo?->desc,
                    'seo_text' => $data->seo?->text,
                    'description' => $data->notes,
                ]
            ]
        ];

//        if($image = $data->seo?->getFirstImage()){
//            /** @var $imageService ImageResourceTransformer */
//            $imageService = resolve(ImageResourceTransformer::class);
//
//            $tmp['seoImage'] = $imageService->toRequest($image);
//        }

        /** @var $imageService ImageResourceTransformer */
        /*$imageService = resolve(ImageResourceTransformer::class);

        if($mainImg = $data->getMainImg()){
            $tmp['mainImage'] = $imageService->toRequest($mainImg);
        }
        foreach ($data->getGallery() ?? [] as $img){
            $tmp['gallery'][] = $imageService->toRequest($img);
        }*/

        foreach ($data->getFeaturesWithValues() ?? [] as $k => $feature){
            $tmp['features'][$k] = [
                'id' => $feature->id,
            ];
            foreach ($feature->inventoryValues as $value){
                $tmp['features'][$k]['values'][] = [
                    'id' => $value->id
                ];
            }
        }

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
