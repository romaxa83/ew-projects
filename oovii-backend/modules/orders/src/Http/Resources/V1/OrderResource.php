<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\Order;
use WezomCms\Providers\Http\Resources\V1\ProviderSimpleResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order Resource",
 *     description="Order Resource",
 * )
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Order $model */
        $model = $this;

        return [
            'id' => $model->id,
            'delivery' => DeliveryResource::make($model->delivery),
            'deliveryInfo' => OrderDeliveryResource::make($model->deliveryInformation),
            'payment' => PaymentResource::make($model->payment),
            // 'paymentInfo' => PaymentInfoResource::make($model->paymentInformation),
            'status' => OrderStatusResource::make($model->status),
            'status_history' => OrderStatusHistoryResource::collection($model->getFullStatusHistory()),
            'user_id' => $model->user_id,
            'recipient' => OrderRecipientResource::make($model),
            'items' => OrderItemResource::collection($model->items),
            'created_at' => $model->created_at->timestamp,
            'provider' => ProviderSimpleResource::make($model->provider),
            'used_bonuses' => $model->usedBonuses(),
            'delivery_cost' => $model->getDeliveryCost(),
            'discount' => $model->discount,
            'sum' => $model->whole_price,
            'total' => $model->getTotalSum(),
            'can_be_reviewed' => $model->canBeReviewed(),
            'can_be_cancelled' => $model->canBeCancelled(),
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID заказа", example=1),
     * @OA\Property(property="delivery", title="Delivery", description="Выбранная доставка", type="object",
     *     ref="#/components/schemas/DeliveryResource"
     * ),
     * @OA\Property(property="deliveryInfo", title="Delivery info", description="Информация о доставке", type="object",
     *     ref="#/components/schemas/OrderDeliveryResource"
     * ),
     * @OA\Property(property="payment", title="Payment", description="Способ оплаты", type="object",
     *     ref="#/components/schemas/PaymentResource"
     * ),
     * @OA\Property(property="status", title="Status", description="Статус заказа", type="object",
     *     ref="#/components/schemas/OrderStatusResource"
     * ),
     * @OA\Property(property="status_history", title="Status history", description="История статусов заказа", type="array",
     *     @OA\Items(ref="#/components/schemas/OrderStatusHistoryResource")
     * ),
     * @OA\Property(property="recipient", title="Recipient", description="Получатель", type="object",
     *     ref="#/components/schemas/OrderRecipientResource"
     * ),
     * @OA\Property(property="items", title="Items", description="Товарные позиции в заказе", type="array",
     *     @OA\Items(ref="#/components/schemas/OrderItemResource")
     * ),
     * @OA\Property(property="user_id", title="User ID", description="ID пользователя", example=1),
     * @OA\Property(property="created_at", title="Created at", description="Время создания заказа", example=1234566543),
     * @OA\Property(property="used_bonuses", title="Used bonuses", description="Сумма использованных для оплаты бонусов", example=350),
     * @OA\Property(property="provider", title="Provider", description="Информация о продавце", type="object",
     *     ref="#/components/schemas/ProviderSimpleResource"
     * ),
     * @OA\Property(property="delivery_cost", title="Delivery cost", description="Стоимость доставки", example=542.15),
     * @OA\Property(property="discount", title="Discount", description="Сумма скидки", example=5151),
     * @OA\Property(property="sum", title="Sum", description="Общая сумма заказа (без скидок)", example=46651),
     * @OA\Property(property="total", title="Total", description="Общая сумма заказа", example=40794),
     * @OA\Property(property="can_be_reviewed", title="Can be reviewed", description="Можно оставить отзыв", example=true),
     * @OA\Property(property="can_be_cancelled", title="Can be cancelled", description="Можно отменить", example=false),
     */
}
