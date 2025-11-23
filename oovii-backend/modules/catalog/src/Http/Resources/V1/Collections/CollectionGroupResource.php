<?php

namespace WezomCms\Catalog\Http\Resources\V1\Collections;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Collections\Category;
use WezomCms\Catalog\Models\Collections\Collection;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Collection group Resource",
 *     description="Collection group Resource",
 * )
 */
class CollectionGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Category */
        $model = $this;
        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'items' => CollectionSimpleResource::collection($model->collections)
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID коллекции", example=1),
     *  @OA\Property(property="name", title="Name", description="Название категории коллекции", example="Collection for real women"),
     *  @OA\Property(property="items", title="Items", description="Коллекции", type="array",
     *       @OA\Items(ref="#/components/schemas/CollectionSimpleResource"))
     *   )
     */
}
