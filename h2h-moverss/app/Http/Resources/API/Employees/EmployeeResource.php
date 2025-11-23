<?php

namespace App\Http\Resources\API\Employees;

use App\Models\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'emails' => EmailResource::collection($this->emails),
            'phones' => PhoneResource::collection($this->phones),
        ];
    }
}
