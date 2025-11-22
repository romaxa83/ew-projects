<?php

namespace App\Http\Resources\BodyShop\Users;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="BSUser", type="object", allOf={
     *      @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email", "status", "role"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="role_id", type="integer", description="User role id"),
     *          @OA\Property(property="full_name", type="string", description="User full name"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="email", type="string", description="User email"),
     *          @OA\Property(property="phone", type="string", description="User phone"),
     *          @OA\Property(property="status", type="string", description="User status"),
     *          @OA\Property(property="last_login", type="integer", description="Last login date in timestamp"),
     *          @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *          @OA\Property(property="phones", type="array", description="User aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          ),
     *          @OA\Property(property="deleted_at", type="integer", description="Time of deleted user", nullable=true),
     *          @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Has related open orders", nullable=true),
     *          @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Has related deleted orders", nullable=true),
     *      )
     * })
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'role_id' => $this->roles->first()->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at instanceof Carbon ? $this->deleted_at->getTimestamp() : null,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenBSOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedBSOrders(),
        ];
    }
}
