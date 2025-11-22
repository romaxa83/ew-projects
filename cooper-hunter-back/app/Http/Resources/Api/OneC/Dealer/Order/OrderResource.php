<?php

namespace App\Http\Resources\Api\OneC\Dealer\Order;

use App\Enums\Orders\OrderArrivedFormEnum;
use App\Http\Resources\Api\OneC\Dealer\DealerResource;
use App\Models\Orders\Dealer\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'arrived_from' => OrderArrivedFormEnum::DEALER,
            'status' => $this->status,
            'type' => $this->type,
            'delivery_type' => $this->delivery_type,
            'payment_type' => $this->payment_type,
            'po' => $this->po,
            'terms' => $this->terms,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'files' => $this->files,
            'tax' => $this->tax,
            'shipping_price' => $this->shipping_price,
            'total' => $this->total,
            'total_discount' => $this->total_discount,
            'total_with_discount' => $this->total_with_discount,
            'invoice' => $this->invoice,
            'invoice_at' => $this->invoice_at?->format('Y-m-d'),
            'has_invoice' => $this->has_invoice,
            'error' => $this->error,
            'approved_at' => $this->approved_at?->format('Y-m-d'),
            'serial_numbers' => OrderSerialNumbersResource::collection($this->serialNumbers),
            'items' => OrderItemResource::collection($this->items),
            'shipping_address' => ShippingAddressResource::make($this->shippingAddress),
            'dealer' => DealerResource::make($this->dealer),
            'packing_slips' => PackingSlipResource::collection($this->packingSlips),
        ];
    }
}
