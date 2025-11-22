<?php

namespace App\Http\Resources\Tags;

use App\Models\Tags\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TagRawShort", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "color"},
 *         @OA\Property(property="id", type="integer", description="Tag id", example=1),
 *         @OA\Property(property="name", type="string", description="Tag title", example="Empty"),
 *         @OA\Property(property="color", type="string", description="Tag color", example="#2F54EB"),
 *     )}
 * )
 *
 * @mixin Tag
 */

class TagShortResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
        ];
    }
}
