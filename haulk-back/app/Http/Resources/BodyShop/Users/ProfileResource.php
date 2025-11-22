<?php

namespace App\Http\Resources\BodyShop\Users;

use App\Http\Resources\Files\ImageResource;
use App\Models\Language;
use App\Models\Users\User;
use App\Services\Permissions\PermissionWorker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     * @throws Exception
     *
     * @OA\Schema(schema="ProfileBS", type="object",
     *     @OA\Property(property="data", type="object", description="Profile data", allOf={
     *         @OA\Schema(required={"id", "first_name", "last_name", "email","role_id"},
     *             @OA\Property(property="id", type="integer", description="User id"),
     *             @OA\Property(property="first_name", type="string", description="User first name"),
     *             @OA\Property(property="last_name", type="string", description="User last name"),
     *             @OA\Property(property="email", type="string", description="User email"),
     *             @OA\Property(property="phone", type="string", description="User phone"),
     *             @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *             @OA\Property(property="phones", type="array", description="User aditional phones",
     *                 @OA\Items(ref="#/components/schemas/PhonesRaw")
     *             ),
     *             @OA\Property(property="role_id", type="integer", description="Role id"),
     *             @OA\Property(property="photo", type="object", description="image with different size", allOf={
     *                 @OA\Schema(ref="#/components/schemas/Image")
     *             }),
     *             @OA\Property(property="permissions",type="object",description="User permissions"),
     *         )}
     *     ),
     * )
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;
        $worker = new PermissionWorker();
        $permissions = $worker->getPermissionsProfile(
            $worker->getUserPermissions($user)
        );

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_extension' => $user->phone_extension,
            'phones' => $user->phones,
            'role_id' => $user->roles->first()->id,
            $user->getImageField() => ImageResource::make($user->getFirstImage()),
            'language' => $user->language ?? Language::default()->first()->slug,
            'permissions' => $permissions,
        ];
    }
}
