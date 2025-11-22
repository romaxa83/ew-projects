<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SimpleDataResource", type="object",
 *     @OA\Property(property="data", type="array", description="data",
 *         @OA\Items(allOf={
 *             @OA\Schema(
 *                 @OA\Property(property="key", type="string",),
 *                 @OA\Property(property="title", type="string",),
 *             )
 *         })
 *     ),
 * )
 */

class SimpleDataResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this['key'],
            'title' => $this['title'],
        ];
    }
}
