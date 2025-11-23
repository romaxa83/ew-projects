<?php

namespace WezomCms\Catalog\Http\Resources\V1\Specifications;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Specifications\SpecValue;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Value Resource (for specification)",
 *     description="Value Resource (for specification)",
 * )
 */
class ValueResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model SpecValue */
        $model = $this;
        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'color' => $model->color,
            'sort' => $model->sort,
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Название характеристики", example="цвет"),
     *  @OA\Property(property="color", title="Color", description="Цвет, актуален для характеристики цвет)", example="#FF0000"),
     */
}
