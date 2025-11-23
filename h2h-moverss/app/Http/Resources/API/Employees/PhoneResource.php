<?php

namespace App\Http\Resources\API\Employees;

use App\Models\Employee\Phone;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Phone
 */
class PhoneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'value' => $this->value,
            'is_primary' => $this->is_primary,
        ];
    }
}


