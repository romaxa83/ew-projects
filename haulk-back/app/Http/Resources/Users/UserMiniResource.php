<?php

namespace App\Http\Resources\Users;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserMiniResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="UserMini", type="object", allOf={
     *      @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email","status","security_level"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="full_name", type="string", description="User full name"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="email", type="string", description="User email"),
     *          @OA\Property(property="role_id", type="integer", description="User role id"),
     *      )
     * })
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role_id' => $this->roles->first()->id,
        ];
    }
}
