<?php

namespace App\Http\Resources\API\Employees\Foremans;

use App\Models\Order\Payroll\Payroll;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payroll
 */
class PayrollResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'cash_collected' => $this->getPaidFromBol()['cash'],
            'cash_paid' => $this->getSumCashPaid(),
            'left_on_hands' => $this->getSumCashPaid(),
            'bol_signed_at' => $this->order->mobileEstimate->bol_signed_at,
            'items' => PayrollItemResource::collection($this->items->sortBy('role_id')),
        ];
    }
}
