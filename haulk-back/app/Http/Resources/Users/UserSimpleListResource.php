<?php

namespace App\Http\Resources\Users;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserSimpleListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="UserSimpleRaw",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "full_name"},
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="full_name", type="string"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="UserSimpleList",
     *     @OA\Property(
     *         property="data",
     *         description="Users short list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/UserSimpleRaw")
     *     ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
        ];
    }
}

