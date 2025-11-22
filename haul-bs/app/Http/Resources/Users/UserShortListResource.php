<?php

namespace App\Http\Resources\Users;

use App\Models\Users\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserRawShort", type="object", allOf={
 *         @OA\Schema(
 *             required={"id", "first_name", "last_name", "email"},
 *             @OA\Property(property="id", type="integer", example=1, description="User id"),
 *             @OA\Property(property="first_name", type="string", example="John", description="User first name"),
 *             @OA\Property(property="last_name", type="string", example="Doe", description="User last name"),
 *             @OA\Property(property="phone", type="string", example="15556666", description="User phone", nullable=true),
 *             @OA\Property(property="phone_extension", type="string", example="4433", description="User phone extension", nullable=true),
 *             @OA\Property(property="email", type="string", example="user@gmail.com", description="User email"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="UserShortListResource",
 *     @OA\Property(property="data", description="Users short list", type="array",
 *         @OA\Items(ref="#/components/schemas/UserRawShort")
 *     ),
 * )
 *
 * @mixin User
 */
class UserShortListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone ? $this->phone->getValue() : null,
            'phone_extension' => $this->phone_extension,
            'email' => $this->email->getValue(),
        ];
    }
}
