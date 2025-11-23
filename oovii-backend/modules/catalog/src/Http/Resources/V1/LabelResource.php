<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Labels\Label;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Product label Resource",
 *     description="Product Resource",
 * )
 */
class LabelResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Label */
        $model = $this;
        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'isGender' => $model->is_gender,
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Название лейбла", example="women"),
     *  @OA\Property(property="isGender", title="Is gender", description="Является ли лейбл , гендерной принадлежностью", example=true),
     */
}
