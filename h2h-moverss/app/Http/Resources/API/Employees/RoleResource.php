<?php

namespace App\Http\Resources\API\Employees;

use App\Models\User\Role;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
        ];
    }
}
