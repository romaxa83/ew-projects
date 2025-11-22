<?php

namespace App\Http\Resources\Tags;

use App\Models\Tags\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tag
 */
class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="Tag",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Tag data",
     *            allOf={
     *                @OA\Schema(
     *                     required={"id", "name", "color", "type", "hasRelatedEntities"},
     *                    @OA\Property(property="id", type="integer", description="Tag id"),
     *                    @OA\Property(property="name", type="string", description="Tag title"),
     *                    @OA\Property(property="color", type="string", description="Tag color"),
     *                    @OA\Property(property="type", type="string", enum={"order"}, description="Tag type"),
     *                    @OA\Property(property="hasRelatedEntities", type="boolean", description="Tag has related entities"),
     *                )
     *            }
     *        ),
     * )
     *
     */
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
