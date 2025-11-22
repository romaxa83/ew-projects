<?php

namespace App\Http\Resources\Inventories\FeatureValue;

use App\Models\Inventories\Features\Value;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryFeatureValueRawShort", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name"},
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="black"),
 *     )}
 * )
 *
 * @OA\Schema(schema="InventoryFeatureValueShortListResource",
 *     @OA\Property(property="data", description="Feature value short list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryFeatureValueRawShort")
 *     ),
 * )
 *
 * @mixin Value
 */
class FeatureValueShortListResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
