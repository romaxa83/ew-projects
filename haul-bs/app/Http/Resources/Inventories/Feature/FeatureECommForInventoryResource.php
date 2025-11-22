<?php

namespace App\Http\Resources\Inventories\Feature;

use App\Http\Resources\Inventories\FeatureValue\FeatureValueECommResource;
use App\Http\Resources\Inventories\FeatureValue\FeatureValueShortListResource;
use App\Models\Inventories\Features\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="FeatureECommForInventoryRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "name", "values"},
 *          @OA\Property(property="id", type="integer", example="1"),
 *          @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *          @OA\Property(property="values", type="array", description="Feature values",
 *              @OA\Items(ref="#/components/schemas/InventoryFeatureValueRawShort")
 *          ),
 *      )}
 * )
 *
 * @mixin Feature
 */

class FeatureECommForInventoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'values' => FeatureValueShortListResource::collection($this->inventoryValues),
        ];
    }
}
