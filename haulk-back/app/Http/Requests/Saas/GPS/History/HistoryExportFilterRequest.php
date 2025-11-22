<?php

namespace App\Http\Requests\Saas\GPS\History;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class HistoryExportFilterRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'truck_id' => ['nullable', 'integer', Rule::exists(Truck::TABLE_NAME, 'id')],
            'trailer_id' => ['nullable', 'integer', Rule::exists(Trailer::TABLE_NAME, 'id')],
            'device_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }
}
