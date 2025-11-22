<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Http\Requests\BaseFormRequest;

/**
 * @bodyParam status string
 */
class OrderListRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', OrderStatus::ruleIn()],
            'page' => ['sometimes', 'int'],
            'per_page' => ['sometimes', 'int'],
        ];
    }
}
