<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTotalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="OrderTotalResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Order data",
     *            allOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="order_total_count", type="integer", description="Total order"),
     *                      @OA\Property(property="total_carrier_amount", type="number", description="Sum by field 'Total amount for the order'"),
     *                      @OA\Property(property="customer_payment_amount", type="number", description="Sum by field 'Customer will pay to carrier'"),
     *                      @OA\Property(property="broker_payment_amount", type="number", description="Sum by field 'Broker will pay to carrier'"),
     *                      @OA\Property(property="broker_fee_amount", type="number", description="Sum by field 'Carrier will pay to broker'")
     *                  )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request): array
    {
        $data = $this->resource;
        return [
            'order_total_count' => $data['total'],
            'total_carrier_amount' => $data['total_carrier_amount'],
            'customer_payment_amount' => $data['customer_amount_forecast'],
            'broker_payment_amount' => $data['broker_amount_forecast'],
            'broker_fee_amount' => $data['broker_fee_amount_forecast'],
            'total_due' => $data['broker_total_due'],
            'current_due' => $data['broker_current_due'],
            'past_due' => $data['broker_past_due'],
            'broker_fee_total_due' => $data['broker_fee_total_due'],
            'broker_fee_current_due' => $data['broker_fee_current_due'],
            'broker_fee_past_due' => $data['broker_fee_past_due'],
        ];
    }
}
