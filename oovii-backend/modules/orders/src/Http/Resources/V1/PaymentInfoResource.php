<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\OrderPaymentInformation;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Payment Information Resource",
 *     description="Payment Information Resource",
 * )
 */
class PaymentInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model OrderPaymentInformation */
        $model = $this;

        return [
            'id' => $model->id,
            'order_ids' => $model->order_ids,
            'payment_payload' => $model->getPaymentDriverPayload(),
            'payment_data' => $model->payment_data,
            'total_sum' => $model->getTotalSum(),
            'orders' => SmallOrderResource::collection($model->orders),
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID платежа", example=1),
     * @OA\Property(property="order_ids", title="Order ids", description="Идентификаторы заказов в платеже, через '-'", example="25-26-27"),
     * @OA\Property(property="payment_payload", title="Payment payload", description="Информация для выбранного платежного сервиса", type="object"),
     * @OA\Property(property="payment_data", title="Payment data", description="Информация о платеже", type="object"),
     * @OA\Property(property="total_sum", title="Total sum", description="Общая сумма платежа", example=5025.36),
     * @OA\Property(property="orders", title="Orders", description="Информация о заказах", type="array",
     *     @OA\Items(ref="#/components/schemas/SmallOrderResource"))
     * ),
     */
}
