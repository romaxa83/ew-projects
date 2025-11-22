<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Permissions\RoleResource;
use App\Models\Users\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SalesManagerRaw", type="object", allOf={
 *       @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email", "status", "role"},
 *           @OA\Property(property="id", type="integer", example=1, description="User id"),
 *           @OA\Property(property="full_name", type="string", example="John Doe", description="User full name"),
 *           @OA\Property(property="first_name", type="string", example="John", description="User first name"),
 *           @OA\Property(property="last_name", type="string", example="Doe", description="User last name"),
 *           @OA\Property(property="email", type="string", example="example@gmail.com", description="User email"),
 *           @OA\Property(property="phone", type="string", example="1555999999", description="User phone", nullable=true),
 *           @OA\Property(property="status", type="string", example="active", description="User status"),
 *           @OA\Property(property="phone_extension", type="string", example="9999", description="User phone extension", nullable=true),
 *           @OA\Property(property="phones", type="array", description="User aditional phones",
 *               @OA\Items(ref="#/components/schemas/PhonesRaw")
 *           ),
 *           @OA\Property(property="deleted_at", type="integer", description="Time of deleted user", nullable=true),
 *       )
 *  })
 *
 * @OA\Schema(schema="SalesMamagerResource",
 *     @OA\Property(property="data", type="object", ref="#/components/schemas/SalesManagerRaw")
 * )
 *
 * @mixin User
 */
class SalesManagerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email->getValue(),
            'phone' => $this->phone ? $this->phone->getValue() : null,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->timestamp : null,
        ];
    }
}
