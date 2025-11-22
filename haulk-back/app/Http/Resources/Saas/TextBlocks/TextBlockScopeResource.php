<?php

namespace App\Http\Resources\Saas\TextBlocks;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class TextBlockScopeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @OA\Schema(
     *     schema="TextBlockScopes",
     *     type="array",
     *     description="List scopes for text block",
     *     @OA\Items (
     *              type="object",
     *                  @OA\Property (property="id", type="string", description="Scope ID"),
     *                  @OA\Property (property="name", type="string", description="Scope name"),
     *     )
     * )
     */
    public function toArray($request): array
    {
        $response = [];
        foreach ($this['scopes'] as $id => $name) {
            $response[] = [
                'id' => $id,
                'name' => $name
            ];
        }
        return $response;
    }
}
