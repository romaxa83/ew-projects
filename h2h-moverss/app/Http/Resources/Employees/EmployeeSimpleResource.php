<?php

namespace App\Http\Resources\Employees;

use App\Models\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeSimpleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->name,
            'last_name' => $this->l_name,
        ];
    }
}
