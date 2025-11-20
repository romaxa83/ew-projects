<?php

namespace App\Resources\User;

use App\Models\User\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="User Profile Resource",
 *     @OA\Property(property="first_name", type="string", description="Имя", example="Cubic"),
 *     @OA\Property(property="last_name", type="string", description="Фамилия", example="Rubic"),
 * )
 */

class UserProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Profile $profile */
        $profile = $this;

        return [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
        ];
    }
}
