<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Specifications\SpecValue;

/**
 * @OA\Schema(
 *     type="object",
 *     title="SpecValue Resource",
 *     description="SpecValue Resource",
 * )
 */
class SpecValueResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model SpecValue */
        $model = $this;
        return [
            'id' => $model->specification->id,
            'name' => $model->specification->translation->name ?? null,
            'type' => $model->specification->type ?? null,
            'slug' => $model->specification->slug ?? null,
            $this->mergeWhen($model->specification->isColor(), [
                'color' => $model->color,
            ]),
            'valueId' => $model->id,
            'value' => $model->translation->name ?? null,
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID", example=1)
     *  @OA\Property(property="name", title="Name", description="Название характеристики", example="Color")
     *  @OA\Property(property="type", title="Type", description="Тип пока потдерживаеться [color]", example="color")
     *  @OA\Property(property="slug", title="Slug", description="Слаг характеристики", example="ves")
     *  @OA\Property(property="color", title="Color", description="HEX цвет (присутствует у характ. с типом color)", example="#FFFF00")
     *  @OA\Property(property="valueId", title="Value ID", description="ID", example=1)
     *  @OA\Property(property="value", title="Value", description="Название значения характеристики", example="grey")
     */
}

