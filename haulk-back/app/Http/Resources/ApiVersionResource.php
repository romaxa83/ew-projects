<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiVersionResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="ApiVersionResource",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="allowed", type="boolean", description=""),
     *                          @OA\Property(property="deprecated", type="boolean", description=""),
     *                          @OA\Property(property="deprecation_message", type="string", description=""),
     *                      )
     *           }
     *           ),
     * )
     */
    public function toArray($request): array
    {
        return [
            'allowed' => $this['allowed'],
            'deprecated' => $this['deprecated'],
            'deprecation_message' => $this['deprecation_message'],
        ];
    }
}
