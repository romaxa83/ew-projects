<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Permissions\RoleResource;
use App\Models\Users\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserProfileResource",
 *     type="object",
 *     allOf={@OA\Schema(
 *          required={"id", "full_name", "first_name", "last_name", "email", "language", "role", "permissions"},
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="full_name", type="string", example="John Doe"),
 *          @OA\Property(property="first_name", type="string", example="John"),
 *          @OA\Property(property="last_name", type="string", example="Doe"),
 *          @OA\Property(property="email", type="string", example="test@test.com"),
 *          @OA\Property(property="phone", type="string", example="15555555555", nullable=true),
 *          @OA\Property(property="phone_extension", type="string", example="4111", nullable=true),
 *          @OA\Property(property="phones", type="array", description="User aditional phones",
 *              @OA\Items(ref="#/components/schemas/PhonesRaw")
 *          ),
 *          @OA\Property(property="language", type="string", example="en"),
 *          @OA\Property(property="role", type="object", ref="#/components/schemas/RoleResource"),
 *          @OA\Property(property="permissions",type="object",description="User permissions"),
 *          @OA\Property(property="photo", type="object", description="image with different size", allOf={
 *              @OA\Schema(ref="#/components/schemas/Image")
 *          }),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="PhonesRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"number", "extension"},
 *         @OA\Property(property="number", type="string", description="Phone number", example="15555555555"),
 *         @OA\Property(property="extension", type="string", description="Phone extension", example="4111"),
 *     )}
 * )
 *
 * @mixin User
 */
class ProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this;

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $this->email->getValue(),
            'phone' => $this->phone ? $this->phone->getValue() : null,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'language' => $this->lang,
            $user->getImageField() => ImageResource::make($user->getFirstImage()),
            'role' => RoleResource::make($this->role),
            'permissions' => $this->getPermissions(),
        ];
    }
}
