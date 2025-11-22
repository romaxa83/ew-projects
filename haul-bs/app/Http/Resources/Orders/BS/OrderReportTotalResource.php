<?php

namespace App\Http\Resources\Orders\BS;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="OrderBSReportTotal", type="object",
 *     @OA\Property(property="data", type="object", allOf={
 *         @OA\Schema(
 *             @OA\Property(property="total_due", type="number"),
 *             @OA\Property(property="current_due", type="number"),
 *             @OA\Property(property="past_due", type="number"),
 *             @OA\Property(property="total_amount", type="number"),
 *             @OA\Property(property="total_profit", type="number"),
 *         )
 *     })
 * )
 */

class OrderReportTotalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'total_due' => $this->total_due,
            'current_due' => $this->current_due,
            'past_due' => $this->past_due,
            'total_amount' => $this->total_amount,
            'total_profit' => $this->total_profit,
        ];
    }
}
