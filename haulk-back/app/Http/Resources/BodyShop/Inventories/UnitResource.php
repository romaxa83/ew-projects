<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Unit
 */
class UnitResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="InventoryUnit",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Inventory inventory data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name", "accept_decimals"},
     *                     @OA\Property(property="id", type="integer", description="Inventory unit id"),
     *                     @OA\Property(property="name", type="string", description="Inventory unit Name"),
     *                     @OA\Property(property="accept_decimals", type="boolean", description="Inventory unit can accept decimals"),
     *                     @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Inventory unit can be deleted"),
     *                 )
     *             }
     *         ),
     * )
     *
     * @OA\Schema(
     *     schema="InventoryUnitRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "name", "accept_decimals"},
     *             @OA\Property(property="id", type="integer", description="Inventory unit id"),
     *             @OA\Property(property="name", type="string", description="Inventory unit Name"),
     *             @OA\Property(property="accept_decimals", type="boolean", description="Inventory unit can accept decimals"),
     *             @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Inventory unit can be deleted"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="InventoryUnitList",
     *     @OA\Property(
     *         property="data",
     *         description="Inventory Unit list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/InventoryUnitRaw")
     *     ),
     * )
     */
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
