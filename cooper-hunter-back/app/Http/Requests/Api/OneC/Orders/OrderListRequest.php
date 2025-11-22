<?php

namespace App\Http\Requests\Api\OneC\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Http\Requests\BaseFormRequest;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderListPermission;
use Illuminate\Validation\Rule;

/**
 * @bodyParam technician_id string Filter by technician ID
 * @bodyParam technician_name string Filter by technician name
 * @bodyParam recipient_name string Filter by recipient name (shipping)
 * @bodyParam date_from string Filter by date from. Format: Y-m-d
 * @bodyParam date_to string Filter by date to. Format: Y-m-d
 */
class OrderListRequest extends BaseFormRequest
{
    public const PERMISSION = OrderListPermission::KEY;

    public function rules(): array
    {
        return [
            'cost_status' => ['sometimes', OrderCostStatusEnum::ruleIn()],
            'status' => ['sometimes', OrderStatusEnum::ruleIn()],
            'technician_id' => ['sometimes', 'required', 'int', Rule::exists(Technician::class, 'id')],
            'technician_name' => ['sometimes', 'required', 'string', 'min:2'],
            'recipient_name' => ['sometimes', 'required', 'string', 'min:2'],
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d'],
            'serial_number' => ['sometimes', 'string'],
        ];
    }
}
