<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Permissions\RoleResource;
use App\Models\Users\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="UserPaginationResource",
 *     @OA\Property(property="data", description="User paginated list", type="object", allOf={
 *         @OA\Schema(required={"id", "full_anme", "first_name", "last_name", "email", "status", "role"},
 *             @OA\Property(property="id", type="integer", example=1, description="User id"),
 *             @OA\Property(property="full_name", type="string", example="John Doe", description="User full name"),
 *             @OA\Property(property="first_name", type="string", example="John", description="User first name"),
 *             @OA\Property(property="last_name", type="string", example="Doe", description="User last name"),
 *             @OA\Property(property="email", type="string", example="user@gmail.com", description="User email"),
 *             @OA\Property(property="phone", type="string", example="15556666", description="User phone", nullable=true),
 *             @OA\Property(property="phone_extension", example="4433", type="string", description="User phone extension", nullable=true),
 *             @OA\Property(property="status", type="string", example="active", description="User status"),
 *             @OA\Property(property="role", type="object", ref="#/components/schemas/RoleResource"),
 *             @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Has related open orders", nullable=true),
 *             @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Has related deleted orders", nullable=true),
 *         )}
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 * @mixin User
 */
class UserPaginationResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email->getValue(),
            'phone' => $user->phone ? $user->phone->getValue() : null,
            'phone_extension' => $user->phone_extension,
            'status' => $user->status,
            'role' => RoleResource::make($this->role),
            'hasRelatedOpenOrders' => false,
            'hasRelatedDeletedOrders' => false,
//            'hasRelatedOpenOrders' => $this->hasRelatedOpenBSOrders(),
//            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedBSOrders(),
        ];
    }
}

