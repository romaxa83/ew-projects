<?php

namespace App\Resources\Country;

use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Country Resource",
 *     @OA\Property(property="id", type="string", example=6),
 *     @OA\Property(property="name", type="string", example = "Andorra"),
 *     @OA\Property(property="active", type="boolean", example=true),
 * )
 */
class CountryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Country $model */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->name,
            'active' => $model->active
        ];
    }
}
