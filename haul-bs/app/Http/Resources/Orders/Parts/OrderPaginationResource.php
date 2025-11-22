<?php

namespace App\Http\Resources\Orders\Parts;

use App\Http\Resources\Customers\CustomerShortListResource;
use App\Http\Resources\Orders\Parts\Additional\ActionScopeResource;
use App\Http\Resources\Orders\Parts\Additional\EcommerceClientResource;
use App\Http\Resources\Users\SalesManagerResource;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderPartsRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Order parts data", allOf={
 *         @OA\Schema(
 *             required={"id", "order_numner", "customer", "status"},
 *             @OA\Property(property="id", type="integer", description="Supplier id"),
 *             @OA\Property(property="order_number", type="string", description="Order number"),
 *             @OA\Property(property="customer", type="object", ref="#/components/schemas/CustomerRawShort"),
 *             @OA\Property(property="sales_manager", type="object", ref="#/components/schemas/SalesManagerRaw"),
 *             @OA\Property(property="status", type="string", description="Order status, (can be get here - /api/v1/orders/parts/catalog/order-statuses)",
 *                 enum={"new", "in_process", "sent", "pending_pickup", "delivered", "canceled", "returned", "lost", "damaged"}
 *             ),
 *             @OA\Property(property="total_amount", type="number", description="Order total amount"),
 *             @OA\Property(property="paid_amount", type="number", description="Order paid amount"),
 *             @OA\Property(property="paid_at", type="integer", description="Paid date"),
 *             @OA\Property(property="refunded_at", type="integer", description="Refunded date"),
 *             @OA\Property(property="source", type="string", description="Order source, (can be get here - /api/v1/orders/parts/catalog/sources)",
 *                 enum={"bs", "amazon", "haulk_depot"}
 *             ),
 *             @OA\Property(property="is_overdue", type="boolean", description="Order status"),
 *             @OA\Property(property="overdue_days", type="integer", description="Overdue days"),
 *             @OA\Property(property="items_count", type="integer", description="Count items"),
 *             @OA\Property(property="comments_count", type="integer", description="Count comments"),
 *             @OA\Property(property="created_at", type="integer", description="Created order"),
 *             @OA\Property(property="delivery_full_address", type="string", description="Delivery address"),
 *             @OA\Property(property="delivery_phone", type="string", description="Phone from delivery address"),
 *             @OA\Property(property="shipping", type="object", ref="#/components/schemas/ShippingRaw"),
 *             @OA\Property(property="delivery", type="array",
 *                 @OA\Items(ref="#/components/schemas/DeliveryRaw")
 *             ),
 *             @OA\Property(property="is_refunded", type="boolean", description="Order is refunded"),
 *             @OA\Property(property="delivery_type", type="string", description="Order delivery type",
 *                 enum={"delivery", "pickup"}
 *             ),
 *             @OA\Property(property="action_scopes", type="object", ref="#/components/schemas/ActionScopeRaw"),
 *             @OA\Property(property="items", description="Order items", type="array",
 *                 @OA\Items(ref="#/components/schemas/OrderPartsItemRaw")
 *             ),
 *             @OA\Property(property="ecommerce_client", type="object", ref="#/components/schemas/EcommerceClientRaw"),
 *         )
 *     }),
 * )
 *
 * @OA\Schema(schema="OrderPartsPaginationResource",
 *     @OA\Property(property="data", description="Order parts paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/OrderPartsRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin Order
 */
class OrderPaginationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer' => CustomerShortListResource::make($this->customer),
            'sales_manager' => SalesManagerResource::make($this->salesManager),
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'paid_at' => $this->paid_at?->timestamp,
            'refunded_at' => $this->refunded_at?->timestamp,
            'source' => $this->source->value,
            'is_overdue' => $this->isOverdue(),
            'overdue_days' => $this->getOverdueDays(),
            'items_count' => $this->items->count(),
            'comments_count' => $this->comments->count(),
            'created_at' => $this->created_at->timestamp,
            'delivery_full_address' => $this->delivery_address?->getFullAddress(),
            'delivery_phone' => $this->delivery_address?->phone->getValue(),
            'shipping' => isset($this->shippingMethods[0])
                ? ShippingResource::make($this->shippingMethods[0])
                : null
            ,
            'delivery' => DeliveryResource::collection($this->deliveries),
            'delivery_type' => $this->delivery_type?->value,
            'is_refunded' => $this->isRefunded(),
            'action_scopes' => ActionScopeResource::make($this),
            'items' => ItemResource::collection($this->items),
            'ecommerce_client' => EcommerceClientResource::make($this->ecommerce_client),
        ];
    }
}
