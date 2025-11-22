<?php

namespace App\Http\Resources\Tags;

use App\Models\Tags\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TagResource", type="object",
 *     @OA\Property(property="data", type="object", description="Tag data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "color", "type", "hasRelatedEntities"},
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Empty"),
 *             @OA\Property(property="color", type="string", example="#2F54EB"),
 *             @OA\Property(property="type", type="string", enum={"trucks_and_trailer", "customer"}, example="trucks_and_trailer"),
 *             @OA\Property(property="hasRelatedEntities", type="boolean", description="Tag has related entities"),
 *         )}
 *     ),
 * )
 *
 * @mixin Tag
 */
class TagResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'type' => $this->type,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
