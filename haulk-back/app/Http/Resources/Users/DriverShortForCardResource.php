<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Fueling\FuelCardPaginatedResource;
use App\Http\Resources\Fueling\FuelCardShortResource;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class DriverShortForCardResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="DriverShortForCardList",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Vehicle type data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "first_name", "last_name", "role_name"},
     *                     @OA\Property(property="id", type="integer", description="User id"),
     *                     @OA\Property(property="first_name", type="string", description="User first name"),
     *                     @OA\Property(property="last_name", type="string", description="User last name"),
     *                     @OA\Property(property="role_id", type="integer", description="User role id"),
     *                     @OA\Property(property="fuel_cards", type="array", description="Fuel cards",
     *                          @OA\Items(ref="#/components/schemas/FuelCardShortResource")
     *                     ),
     *                 )
     *             }
     *         ),
     *  )
     *
     * @OA\Schema(schema="DriverShortForCard", type="object", allOf={
     *       @OA\Schema(required={"id", "first_name", "last_name", "role_name"},
     *           @OA\Property(property="id", type="integer", description="User id"),
     *           @OA\Property(property="first_name", type="string", description="User first name"),
     *           @OA\Property(property="last_name", type="string", description="User last name"),
     *           @OA\Property(property="role_id", type="string", description="User role id"),
     *           @OA\Property(property="fuel_cards", type="array", description="Fuel cards",
     *              @OA\Items(ref="#/components/schemas/FuelCardShortResource")
     *           ),
     *       )
     *  })
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role_id' => $this->roles->first()->id,
            'fuel_cards' => FuelCardShortResource::collection($this->fuelCards),
        ];
    }
}
