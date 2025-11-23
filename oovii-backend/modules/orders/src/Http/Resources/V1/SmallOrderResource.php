<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\Order;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Small Order Resource",
 *     description="Small Order Resource",
 * )
 */
class SmallOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var $model Order */
        $model = $this;

        return [
            'id' => $model->id,
            'payed' => $model->payed,
            'sum' => $model->getSumForPay(),
        ];
    }

    /**
     * @OA\Property(property="id", title="Order ID", description="ID заказа", example=2354),
     * @OA\Property(property="payed", title="Payed", description="Статус оплаты заказа", example=false),
     * @OA\Property(property="sum", title="Sum", description="Сумма к оплате", example=5850),
     */
}
