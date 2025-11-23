<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Brand;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Brand Resource (for product)",
 *     description="Brand Resource (for product)",
 * )
 */
class BrandResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Brand */
        $model = $this;
        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'image' => $model->getImageUrl(),
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Название бренда", example="adidas"),
     *  @OA\Property(property="image", title="Image", description="Ссылка на картинку бренда",
     *      example="http://192.168.175.1/storage/products/images/small/6hlcMTfi075vHncJFwLJzgoXwCnwU3Xsvtr0e7fK.png?v=1644395573")
     */
}
