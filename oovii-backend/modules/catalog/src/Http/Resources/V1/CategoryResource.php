<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Category;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Category Resource (for product)",
 *     description="Category Resource (for product)",
 * )
 */
class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Category */
        $model = $this;
        return [
            'id' => $model->id,
            'name' => $model->translation->name ?? null,
            'description' => $model->translation->text ?? null,
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Название категории", example="adidas"),
     *  @OA\Property(property="description", title="Description", description="Описание", example="category descrtiption"),
     */
}
