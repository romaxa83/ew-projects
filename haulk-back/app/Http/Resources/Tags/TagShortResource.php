<?php

namespace App\Http\Resources\Tags;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagShortResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     * @OA\Schema(
     *    schema="TagRawShort",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                    required={"id", "name", "color", "type", "hasRelatedEntities"},
     *                    @OA\Property(property="id", type="integer", description="Tag id"),
     *                    @OA\Property(property="name", type="string", description="Tag title"),
     *                    @OA\Property(property="color", type="string", description="Tag color"),
     *                )
     *        }
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
        ];
    }
}
