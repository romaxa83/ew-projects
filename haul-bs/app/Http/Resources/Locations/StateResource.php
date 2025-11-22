<?php

namespace App\Http\Resources\Locations;

use App\Foundations\Modules\Location\Models\State;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="StateRaw", type="object", allOf={
 *     @OA\Schema(
 *         @OA\Property(property="id", type="int", example=1),
 *         @OA\Property(property="name", type="string", example="Florida"),
 *         @OA\Property(property="status", type="boolean", example=true),
 *         @OA\Property(property="short", type="string", example="FL"),
 *         @OA\Property(property="country_code", type="string", example="us"),
 *         @OA\Property(property="country_name", type="string", example="USA"),
 *     )}
 * )
 *
 * @OA\Schema(schema="StateResource",
 *     @OA\Property(property="data", description="State list", type="array",
 *         @OA\Items(ref="#/components/schemas/StateRaw")
 *     ),
 * )
 *
 * @mixin State
 */

class StateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->active,
            'short' => $this->state_short_name,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
        ];
    }
}
