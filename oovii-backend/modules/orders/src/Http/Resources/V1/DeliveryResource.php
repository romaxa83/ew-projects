<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\Delivery;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Delivery Resource",
 *     description="Delivery Resource",
 * )
 */
class DeliveryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Delivery */
        $model = $this;

        return [
            'id' => $model->id,
            'sort' => $model->sort,
            'driver' => $model->driver,
            'name' => $model->translation->name,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID доставки", example=1),
     * @OA\Property(property="name", title="Name", description="Название доставки", example="СДЭК"),
     * @OA\Property(property="sort", title="Sort", description="Сортировка видов доставки", example="1"),
     * @OA\Property(property="driver", title="Driver службы доставки", description="Driver службы доставки", example="sdek-courier"),
     */
}
