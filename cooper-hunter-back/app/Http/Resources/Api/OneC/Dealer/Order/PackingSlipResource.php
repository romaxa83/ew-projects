<?php

namespace App\Http\Resources\Api\OneC\Dealer\Order;

use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PackingSlip
 */
class PackingSlipResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'status' => $this->status,
            'number' => $this->number,
            'tracking_number' => $this->tracking_number,
            'tracking_company' => $this->tracking_company,
            'tax' => $this->tax,
            'shipping_price' => $this->shipping_price,
            'total' => $this->total,
            'total_discount' => $this->total_discount,
            'total_with_discount' => $this->total_with_discount,
            'invoice' => $this->invoice,
            'invoice_at' => $this->invoice_at?->format('Y-m-d'),
            'shipped_at' => $this->shipped_at?->format('Y-m-d'),
            'files' => $this->files,
            'dimensions' => DimensionsResource::collection($this->dimensions),
            'serial_numbers' => OrderSerialNumbersResource::collection($this->serialNumbers),
            'items' => PackingSlipItemResource::collection($this->items),
        ];
    }
}
