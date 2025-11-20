<?php

namespace App\Http\Resources\Sips;

use App\Models\Sips\Sip;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="SipResource",
 *     @OA\Property(property="id", type="string", example=1),
 *     @OA\Property(property="number", type="string", example="390")
 * )
 */
class SipResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Sip $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'number' => $model->number,
        ];
    }
}
