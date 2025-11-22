<?php

namespace App\Http\Resources\Saas\GPS\History;

use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Users\UserSimpleListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryAdditionalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'total_mileage' => $this['total_mileage'],
            'drivers' => UserSimpleListResource::collection(($this['drivers'])),
        ];
    }
}

/**
 * @OA\Schema(schema="HistoryAdditionalRawResource", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="total_mileage", type="float", example=1.5),
 *             @OA\Property(property="drivers", type="array", @OA\Items(ref="#/components/schemas/UserSimpleRaw")),
 *         )
 *     }
 * )
 *
 *
 * @OA\Schema(schema="HistoryAdditionalResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/HistoryAdditionalRawResource")
 *         }
 *     )
 * )
 *
 */


