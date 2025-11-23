<?php

namespace WezomCms\Catalog\Http\Resources\V1\Specifications;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Specifications\Specification;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Specification Resource (for product)",
 *     description="Specification Resource (for product)",
 * )
 */
class SpecificationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Specification */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'type' => $model->type,
            'image' => $model->getImageUrl(),
            'values' => ValueResource::collection($model->specValues)
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID товара", example=1),
     * @OA\Property(property="name", title="Name", description="Название характеристики", example="цвет"),
     * @OA\Property(property="type", title="Type", description="Тип характеристики (на данный момент есть только - color, чтоб понимать что это цвет)", example="color"),
     * @OA\Property(property="image", title="Image", description="Ссылка на картинку бренда",
     *      example="http://192.168.175.1/storage/products/images/small/6hlcMTfi075vHncJFwLJzgoXwCnwU3Xsvtr0e7fK.png?v=1644395573"
     *  )
     * @OA\Property(property="values", title="Values", description="Значения данной характеристики", type="object",
     *      ref="#/components/schemas/ValueResource"
     *  )
     */
}

