<?php

namespace App\Http\Resources\BodyShop\Inventories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionsReportTotalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="InventoryTransactionsReportTotalBS",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="price_total", type="number"),
     *                @OA\Property(property="cost_total", type="number"),
     *            )
     *        }
     *    )
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'price_total' => round($this->price_total, 2),
            'cost_total' => round($this->cost_total, 2),
        ];
    }
}
