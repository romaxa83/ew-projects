<?php

namespace App\Http\Resources\Users;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserShortListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="UserRawShort",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "phone", "first_name", "last_name", "email"},
     *             @OA\Property(property="id", type="integer", description="Vehicle Owner id"),
     *             @OA\Property(property="first_name", type="string", description="Vehicle Owner First Name"),
     *             @OA\Property(property="last_name", type="string", description="Vehicle Owner Last Name"),
     *             @OA\Property(property="phone", type="string", description="Vehicle Owner Phone"),
     *             @OA\Property(property="phone_extension", type="string", description="Vehicle Owner Phone Extension"),
     *             @OA\Property(property="email", type="string", description="Vehicle Owner Email"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="UserShortList",
     *     @OA\Property(
     *         property="data",
     *         description="Users short list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/UserRawShort")
     *     ),
     * )
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'phone_extension' => $this->phone_extension,
            'email' => $this->email,
        ];
    }
}
