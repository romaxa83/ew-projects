<?php

namespace App\Http\Resources\API\Employees;

use App\Models\Employee\Email;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Email
 */
class EmailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'value' => $this->value,
            'is_primary' => $this->is_primary,
        ];
    }
}

