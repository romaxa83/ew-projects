<?php

namespace App\Http\Resources\BodyShop\Users;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="BSUserRaw",
     *   @OA\Property(property="data", description="User paginated list", type="object", allOf={
     *      @OA\Schema(required={"id", "full_anme", "first_name", "last_name", "email","status"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="full_name", type="string", description="User full name"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="email", type="string", description="User email"),
     *          @OA\Property(property="phone", type="string", description="User phone"),
     *          @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *          @OA\Property(property="status", type="string", description="User status"),
     *          @OA\Property(property="last_login", type="integer", description=""),
     *          @OA\Property(property="role_id", type="integer", description="User role id"),
     *          @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Has related open orders", nullable=true),
     *          @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Has related deleted orders", nullable=true),
     *       )
     *     }
     *   ),
     *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_extension' => $user->phone_extension,
            'status' => $user->status,
            'last_login' => $user->lastLogin ? $user->lastLogin->created_at->timestamp : null,
            'role_id' => $this->roles->first()->id,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenBSOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedBSOrders(),
        ];
    }
}
