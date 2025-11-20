<?php

namespace App\Resources\User;

use App\Helpers\DateFormat;
use App\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="User Simple Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="login", type="string", description="Login", example="cubic"),
 *     @OA\Property(property="email", type="string", description="Email", example="cubic@rubic.com"),
 *     @OA\Property(property="phone", type="string", description="Телефон", example="+380500000001"),
 *     @OA\Property(property="status", type="boolean", description="Status", example=true),
 *     @OA\Property(property="profile", type="object", description="Profile",
 *         ref="#/components/schemas/UserProfileResource"
 *     ),
 * )
 */
class UserSimpleResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this;

        return [
            'id' => $user->id,
            'login' => $user->login,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'profile' => UserProfileResource::make($user->profile),
            'created' => DateFormat::front($user->created_at),
            'updated' => DateFormat::front($user->updated_at),
        ];
    }
}
