<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class SalesTeamUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_plans.local.*.sales_plan_id' => ['nullable', 'int'],
            'sales_plans.local.*.local' => ['nullable', 'int'],
            'sales_plans.local.*.intrestate' => ['nullable', 'int'],
            'sales_plans.local.*.date' => ['nullable', 'string'],
            'sales_plans.long.*.sales_plan_id' => ['nullable', 'int'],
            'sales_plans.long.*.local' => ['nullable', 'int'],
            'sales_plans.long.*.intrestate' => ['nullable', 'int'],
            'sales_plans.long.*.date' => ['nullable', 'string'],
            'efficiency_plan.id' => ['nullable', 'int'],
            'efficiency_plan.conversion_local_team' => ['nullable', 'int'],
            'efficiency_plan.conversion_long_team' => ['nullable', 'int'],
        ];
    }
}
