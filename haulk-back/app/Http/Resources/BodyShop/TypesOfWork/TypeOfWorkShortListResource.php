<?php

namespace App\Http\Resources\BodyShop\TypesOfWork;

use App\Models\BodyShop\Orders\TypeOfWork;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TypeOfWork
 */
class TypeOfWorkShortListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TypeOfWorkRawShort",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "name",},
     *             @OA\Property(property="id", type="integer", description="Type Of Work id"),
     *             @OA\Property(property="name", type="string", description="Type Of Work Name"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="TypeOfWorkShortList",
     *     @OA\Property(
     *         property="data",
     *         description="Type Of Work short list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/TypeOfWorkRawShort")
     *     ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
