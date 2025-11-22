<?php

namespace App\Http\Resources\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Admin
 */
class AdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}

/**
 * @OA\Schema(schema="AdminResourceRaw", type="object", allOf={
 *            @OA\Schema(
 *                @OA\Property(property="id", type="integer"),
 *                @OA\Property(property="status", type="boolean"),
 *                @OA\Property(property="full_name", type="string"),
 *                @OA\Property(property="email", type="string"),
 *                @OA\Property(property="phone", type="string"),
 *                @OA\Property(property="permission", type="array",
 *                      @OA\Items(ref="#/components/schemas/PermissionForItemRaw")
 *                ),
 *            )
 *        }
 * )
 */

/**
 * @OA\Schema(schema="AdminResource", type="object",
 *        @OA\Property(property="data", type="object",
 *              allOf={@OA\Schema(
 *                @OA\Property(property="id", type="integer"),
 *                @OA\Property(property="status", type="boolean"),
 *                @OA\Property(property="full_name", type="string"),
 *                @OA\Property(property="email", type="string"),
 *                @OA\Property(property="phone", type="string"),
 *            )}),
 * )
 */
