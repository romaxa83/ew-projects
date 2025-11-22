<?php

namespace App\Http\Resources\TypeOfWorks;

use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TypeOfWorkShortListResource", type="object",
 *     @OA\Property(property="data", type="object", description="Type Of Work data", allOf={
 *         @OA\Schema(
 *             required={"id", "name"},
 *             @OA\Property(property="id", type="integer", description="Type Of Work id"),
 *             @OA\Property(property="name", type="string", description="Type Of Work Name"),
 *         )
 *     }),
 * )
 *
 * @mixin TypeOfWork
 */
class TypeOfWorkShortListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
