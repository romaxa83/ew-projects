<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleTypesListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="VehicleTypesListResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Vehicle type data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"id", "title"},
     *                        @OA\Property(property="id", type="integer", description="Vehicle type id"),
     *                        @OA\Property(property="title", type="string", description="Vehicle type name"),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'title' => $this['title'],
        ];
    }
}
