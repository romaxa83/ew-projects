<?php

namespace App\Http\Resources\Saas\Support;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class StatusesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema (
     *     schema="SupportRequestStatuses",
     *     @OA\Property (
     *          property="data",
     *          type="array",
     *          @OA\Items (
     *              allOf={
     *                  @OA\Schema (
     *                      @OA\Property (property="id", type="integer", description="Status Id"),
     *                      @OA\Property (property="name", type="string", description="Status name")
     *                  )
     *              }
     *          )
     *     )
     * )
     */
    public function toArray($request): array
    {
        $response = [];
        foreach ($this['statuses'] as $id => $name) {
            $response[] = [
                'id' => $id,
                'name' => $name
            ];
        }
        return $response;
    }
}
