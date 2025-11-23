<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Employees\EmployeeSimpleResource;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserSimpleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'employee' => EmployeeSimpleResource::make($this->employee),
        ];
    }
}
