<?php

namespace App\Http\Resources\Saas\TextBlocks;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class TextBlockGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @OA\Schema(
     *     schema="TextBlockGroups",
     *     type="array",
     *     description="List groups for text block",
     *     @OA\Items (
     *              type="object",
     *                  @OA\Property (property="id", type="string", description="Group ID"),
     *                  @OA\Property (property="name", type="string", description="Group name"),
     *     )
     * )
     */
    public function toArray($request): array
    {
        $response = [];
        foreach ($this['groups'] as $id => $name) {
            $response[] = [
                'id' => $id,
                'name' => $name
            ];
        }
        return $response;
    }
}
