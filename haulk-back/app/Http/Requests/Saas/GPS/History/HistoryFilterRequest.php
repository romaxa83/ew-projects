<?php

namespace App\Http\Requests\Saas\GPS\History;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Traits\Requests\OnlyValidateForm;

class HistoryFilterRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'device_id' => ['nullable', 'integer'],
            'truck_id' => ['nullable', 'integer'],
            'trailer_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'id' => ['nullable', 'integer'],
            'event_type' => ['nullable', 'string'],
            'alert_type' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'max:' . config('admins.paginate.max_per_page')],
            'order_type' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
