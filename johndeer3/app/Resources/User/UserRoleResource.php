<?php

namespace App\Resources\User;

use App\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="User Role Resource",
 *     @OA\Property(property="role", type="string", description="Роль", example="Спеціаліст з продукту дилера"),
 *     @OA\Property(property="alias", type="string", description="Алиас", example="ps"),
 * )
 */

class UserRoleResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this;

        return [
            'role' => $user->getRoleName(),
            'alias' => $user->getRole()
        ];
    }
}
