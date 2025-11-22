<?php

namespace App\Http\Resources\Inventories\Unit;

use App\Models\Inventories\Unit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryUnitRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "accept_decimals"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="inch"),
 *         @OA\Property(property="accept_decimals", type="boolean", example="true", description="Inventory unit can accept decimals"),
 *         @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory unit can be deleted"),
 *     )}
 * )
 *
 * @OA\Schema(schema="UnitListResource",
 *     @OA\Property(property="data", description="Inventory Unit list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryUnitRaw")
 *     ),
 * )
 *
 * @OA\Schema(schema="UnitResource", type="object",
 *     @OA\Property(property="data", type="object", description="Tag data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "accept_decimals"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="inch"),
 *             @OA\Property(property="accept_decimals", type="boolean", example="true", description="Inventory unit can accept decimals"),
 *             @OA\Property(property="hasRelatedEntities", type="boolean", example="true", description="Is Inventory unit can be deleted"),
 *         )}
 *     ),
 * )
 *
 * @mixin Unit
 */

class UnitResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'accept_decimals' => $this->accept_decimals,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
