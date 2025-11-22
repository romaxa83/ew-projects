<?php

namespace App\Http\Resources\Inventories\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TransactionsReportTotal", type="object",
 *     @OA\Property(property="data", type="object", allOf={
 *         @OA\Schema(
 *             @OA\Property(property="price_total", type="number"),
 *             @OA\Property(property="cost_total", type="number"),
 *         )}
 *     )
 * )
 */

class TransactionReportTotalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'price_total' => round($this->price_total, 2),
            'cost_total' => round($this->cost_total, 2),
        ];
    }
}
