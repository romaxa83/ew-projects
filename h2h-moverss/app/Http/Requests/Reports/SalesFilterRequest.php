<?php

namespace App\Http\Requests\Reports;

use App\Enums\Employee\SalesTeamEnum;
use App\Enums\Orders\MoveTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class SalesFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'filter.date' => ['nullable', 'string', 'date_format:"Y-m"'],
            'filter.date' => ['nullable', 'string'],
            'filter.start-range' => ['nullable', 'string', 'date_format:"Y-m-d"'],
            'filter.end-range' => ['nullable', 'string', 'date_format:"Y-m-d"'],
            'filter.period-type' => ['nullable', 'string'],
            'filter.sales_team' => ['nullable', 'string', SalesTeamEnum::ruleIn()],
            'filter.move_type' => ['nullable', 'string', MoveTypeEnum::ruleIn()],
            'filter.division_id' => ['nullable', 'string', 'max:255'],
            'filter.division_tz' => ['nullable', 'string', 'max:255'],
        ];
    }
}

