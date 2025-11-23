<?php

namespace App\Http\Resources\API\Employees\Foremans;

use App\Models\CashRegistry\CashRegistryItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CashRegistryItem
 */
class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sum' => $this->sum,
            'type' => $this->type->label(),
            'insert_at' => $this->insert_date,
            'cash_on_hands' => $this->balance,
        ];
    }
}
