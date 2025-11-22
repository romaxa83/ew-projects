<?php

namespace App\Http\Resources\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VehicleOwner
 */
class VehicleOwnerShortResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="VehicleOwnerShort", type="object", allOf={
     *      @OA\Schema(required={"id", "fisrt_name", "last_name"},
     *          @OA\Property(property="id", type="integer", description="Vehicle Owner id"),
     *          @OA\Property(property="first_name", type="string", description="Vehicle Owner first name"),
     *          @OA\Property(property="last_name", type="string", description="Vehicle Owner last name"),
     *          @OA\Property(property="phone", type="string", description="Vehicle Owner last phone"),
     *          @OA\Property(property="email", type="string", description="Vehicle Owner last email"),
     *          @OA\Property(property="phone_extension", type="string", description="Vehicle Owner phone_extension"),
     *      )
     * })
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'phone_extension' => $this->phone_extension,
        ];
    }
}
