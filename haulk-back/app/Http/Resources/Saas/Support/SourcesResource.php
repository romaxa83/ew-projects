<?php

namespace App\Http\Resources\Saas\Support;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class SourcesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema (
     *     schema="SupportRequestSources",
     *     @OA\Property (
     *          property="data",
     *          type="array",
     *          @OA\Items (
     *              allOf={
     *                  @OA\Schema (
     *                      @OA\Property (property="id", type="integer", description="Source Id"),
     *                      @OA\Property (property="name", type="string", description="Source name")
     *                  )
     *              }
     *          )
     *     )
     * )
     */
    public function toArray($request): array
    {
        $response = [];
        foreach ($this['sources'] as $id => $name) {
            $response[] = [
                'id' => $id,
                'name' => $name
            ];
        }
        return $response;
    }
}
