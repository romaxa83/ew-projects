<?php

namespace App\Http\Resources\API\Employees\Foremans;


use App\Http\Resources\API\Employees\RoleResource;
use App\Models\Order\Payroll\Item;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Item
 */
class PayrollItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee' => $this->employee->full_name,
            'role' => RoleResource::make($this->role),
            'cash_on_hands' => $this->getCashPaid()
        ];
    }
}

