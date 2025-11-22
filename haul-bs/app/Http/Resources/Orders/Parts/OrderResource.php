<?php

namespace App\Http\Resources\Orders\Parts;

use App\Foundations\Http\Resources\Common\Locations\AddressResource;
use App\Http\Resources\Customers\CustomerShortListResource;
use App\Http\Resources\Orders\Parts\Additional\ActionScopeResource;
use App\Http\Resources\Orders\Parts\Additional\EcommerceClientResource;
use App\Http\Resources\Users\SalesManagerResource;
use App\Models\Orders\Parts\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderPartsResource", type="object",
 *     @OA\Property(property="data", type="object", description="Order data", allOf={
 *         @OA\Schema(
 *             required={"id", "order_number", "status"},
 *             @OA\Property(property="id", type="integer", description="Order id"),
 *             @OA\Property(property="order_number", type="string", description="Order number"),
 *             @OA\Property(property="customer", ref="#/components/schemas/CustomerRawShort"),
 *             @OA\Property(property="sales_manager", ref="#/components/schemas/SalesManagerRaw"),
 *             @OA\Property(property="status", type="string", description="Order status"),
 *             @OA\Property(property="paid_at", type="integer", description="Paid date"),
 *             @OA\Property(property="status_changed_at", type="integer", description="Date/time when status was changed"),
 *             @OA\Property(property="items", description="Order items", type="array",
 *                 @OA\Items(ref="#/components/schemas/OrderPartsItemRaw")
 *             ),
 *             @OA\Property(property="delivery_address", type="object", ref="#/components/schemas/AddressRaw"),
 *             @OA\Property(property="billing_address", type="object", ref="#/components/schemas/AddressRaw"),
 *             @OA\Property(property="delivery_type", type="string", enum={"delivery", "pickup"}),
 *             @OA\Property(property="shipping_methods", type="array",
 *                 @OA\Items(ref="#/components/schemas/ShippingRaw")
 *             ),
 *             @OA\Property(property="payments", type="array",
 *                 @OA\Items(ref="#/components/schemas/PaymentResourceRawParts")
 *             ),
 *             @OA\Property(property="deliveries", type="array",
 *                 @OA\Items(ref="#/components/schemas/DeliveryRaw")
 *             ),
 *             @OA\Property(property="payment", type="object", ref="#/components/schemas/OrderPartsPaymentRaw"),
 *             @OA\Property(property="source", type="string", description="Order source", enum={"bs", "amazon", "haulk_depot"}),
 *             @OA\Property(property="is_refunded", type="boolean", description="Order is refunded"),
 *             @OA\Property(property="is_draft", type="boolean", description="Order is draft state"),
 *             @OA\Property(property="has_free_shipping_inventory", type="boolean"),
 *             @OA\Property(property="has_paid_shipping_inventory", type="boolean"),
 *             @OA\Property(property="has_overload_inventory", type="boolean"),
 *             @OA\Property(property="can_refunded", type="boolean", description="You can perform an action - refunded"),
 *             @OA\Property(property="can_change_status", type="boolean", description="Is it possible to change the status"),
 *             @OA\Property(property="can_canceled", type="boolean", description="Is it possible to cancel an order"),
 *             @OA\Property(property="inventory_amount", type="number", description="Inventory amount"),
 *             @OA\Property(property="tax_amount", type="number", description="Tax amount"),
 *             @OA\Property(property="total_amount", type="number", description="Total amount"),
 *             @OA\Property(property="subtotal_amount", type="number", description="Subtotal amount"),
 *             @OA\Property(property="delivery_amount", type="number", description="Delivery amount"),
 *             @OA\Property(property="delivery_cost", type="number", description="Custom delivery cost by order"),
 *             @OA\Property(property="action_scopes", type="object", ref="#/components/schemas/ActionScopeRaw"),
 *             @OA\Property(property="ecommerce_client", type="object", ref="#/components/schemas/EcommerceClientRaw"),
 *         )
 *     }),
 * )
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        $payment = null;
        if($this->payment_method){
            $payment = [
                'method' => $this->payment_method,
                'terms' => $this->payment_terms,
                'with_tax_exemption' => $this->with_tax_exemption,
            ];
        }
        $totalOnlyItems = $this->getTotalOnlyItems();
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer' => CustomerShortListResource::make($this->customer),
            'sales_manager' => SalesManagerResource::make($this->salesManager),
            'status' => $this->status->value,
            'paid_at' => $this->paid_at?->timestamp,
            'status_changed_at' => $this->status_changed_at?->timestamp,
            'items' => ItemResource::collection($this->items),
            'delivery_address' => AddressResource::make($this->delivery_address),
            'billing_address' => AddressResource::make($this->billing_address),
            'delivery_type' => $this->delivery_type?->value,
            'shipping_methods' => ShippingResource::collection($this->shippingMethods),
            'payment' => $payment,
            'payments' => OrderPaymentResource::collection($this->payments),
            'source' => $this->source->value,
            'is_refunded' => $this->isRefunded(),
            'is_draft' => $this->isDraft(),
            'has_free_shipping_inventory' => $this->hasFreeShippingInventory(),
            'has_paid_shipping_inventory' => $this->hasPaidShippingInventory(),
            'has_overload_inventory' => $this->hasOverloadInventory(),
            'deliveries' => DeliveryResource::collection($this->deliveries),
            'inventory_amount' => $totalOnlyItems,
            'tax_amount' => $this->getTax(),
            'total_amount' => $this->getAmount(),
            'subtotal_amount' => $this->getSubtotal(),
            'delivery_amount' => round($this->getTotalDelivery($totalOnlyItems) - $this->delivery_cost, 2),
            'saving_amount' => $this->getSavingAmount(),
            'action_scopes' => ActionScopeResource::make($this),
            'ecommerce_client' => EcommerceClientResource::make($this->ecommerce_client),
            'delivery_cost' => $this->delivery_cost,
        ];
    }
}
