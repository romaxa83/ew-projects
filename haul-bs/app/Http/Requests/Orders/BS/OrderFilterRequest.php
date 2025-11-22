<?php

namespace App\Http\Requests\Orders\BS;

use App\Enums\Orders\BS\OrderPaymentStatus;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Validation\Rule;

class OrderFilterRequest extends BaseFormRequest
{
    private const PER_PAGE = 10;

    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'vehicle_year' => ['nullable', 'string', 'max:4'],
            'vehicle_make' => ['nullable', 'string'],
            'vehicle_model' => ['nullable', 'string'],
            'mechanic_id' => ['nullable', 'integer', Rule::exists(User::TABLE, 'id'),],
            'status' => ['nullable', 'string', EnumHelper::ruleIn(OrderStatus::class)],
            'payment_status' => ['nullable', 'string', EnumHelper::ruleIn(OrderPaymentStatus::class)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'inventory_id' => ['nullable', 'integer', Rule::exists(Inventory::TABLE, 'id')],
            'truck_id' => ['nullable', 'integer', Rule::exists(Truck::TABLE, 'id')],
            'trailer_id' => ['nullable', 'integer', Rule::exists(Trailer::TABLE, 'id')],
        ];
    }
}
