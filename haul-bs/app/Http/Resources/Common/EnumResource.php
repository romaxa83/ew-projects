<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="EnumResource", type="object",
 *     @OA\Property(property="data", type="array", description="Enum data",
 *         @OA\Items(allOf={
 *             @OA\Schema(
 *                 @OA\Property(property="key", type="string",),
 *                 @OA\Property(property="title", type="string",),
 *             )
 *         })
 *     ),
 * )
 *
 * @return array
 */
class EnumResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'key' => $this['value'],
            'title' => $this['label'],
        ];
    }
}
