<?php

namespace App\Http\Resources\API\Employees\Foremans;

use App\Models\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class ForemanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'cash_on_hands' => $this->cashRegistry->cash_on_hand,
            'last_cash_updated_at' => $this->cashRegistry->updated_at,
        ];
    }
}

