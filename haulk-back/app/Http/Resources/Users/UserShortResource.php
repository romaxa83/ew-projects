<?php

namespace App\Http\Resources\Users;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserShortResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="UserShort", type="object", allOf={
     *      @OA\Schema(required={"id", "first_name", "last_name", "phone"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="phone", type="string", description="User phone"),
     *          @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *          @OA\Property(property="email", type="string", description="User email"),
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
            'phone_extension' => $this->phone_extension,
            'email' => $this->email,
        ];
    }
}
