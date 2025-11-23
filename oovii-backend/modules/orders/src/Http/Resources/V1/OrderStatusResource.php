<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\OrderStatus;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order Status Resource",
 *     description="Order Status Resource",
 * )
 */
class OrderStatusResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var OrderStatus $model */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->name,
            'color' => $model->color,
        ];
    }

    /**
    * @OA\Property(property="id", title="ID", description="ID статуса заказа", example=1),
    * @OA\Property(property="name", title="Name", description="Наименование статуса", example="Новый"),
    * @OA\Property(property="color", title="Color", description="Цвет статуса", example="#880000"),
    */
}
