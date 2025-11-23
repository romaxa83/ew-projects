<?php

namespace App\Http\Requests\Reports;

use App\Enums\Employee\SalesTeamEnum;
use Illuminate\Foundation\Http\FormRequest;

class SalesFunelFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_end' => ['nullable', 'string', 'date_format:"Y-m-d"'],
            'date_start' => ['nullable', 'string', 'date_format:"Y-m-d"'],
            'sales_team' => ['nullable', 'string', SalesTeamEnum::ruleIn().',na,all'],
            'user_id' => ['nullable', 'numeric'],
        ];
    }
}
