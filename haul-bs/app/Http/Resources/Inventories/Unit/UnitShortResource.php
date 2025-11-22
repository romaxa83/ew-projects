<?php

namespace App\Http\Resources\Inventories\Unit;

use App\Models\Inventories\Unit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryUnitShortRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "accept_decimals"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="inch"),
 *         @OA\Property(property="accept_decimals", type="boolean", example="true", description="Inventory unit can accept decimals"),
 *     )}
 * )
 *
 * @mixin Unit
 */

class UnitShortResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'accept_decimals' => $this->accept_decimals,
        ];
    }
}
