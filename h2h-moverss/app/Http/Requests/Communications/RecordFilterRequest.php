<?php

namespace App\Http\Requests\Communications;

use App\Enums\Communications\Filter\EntityEnum;
use App\Enums\Communications\Filter\PeriodEnum;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filters' => ['array'],
            'filters.mode' => ['nullable', 'in:all'],
            'filters.contacts' => ['nullable', 'in:all,myclients,unassigned'],
            'filters.communications' => ['nullable', 'in:all,unanswered'],
            'filters.untill' => ['nullable', 'integer'],
            'filters.ignoreList' => ['nullable', 'array'],
            'filters.channels' => ['nullable', 'array'],
            'filters.searchTerm' => ['nullable', 'string'],
            'filters.period' => ['nullable', 'string', PeriodEnum::ruleIn()],
            'filters.entity' => ['nullable', 'string', EntityEnum::ruleIn()],
            'filters.starred' => ['in:all,starred,notstarred'],
            'filters.responsible' => ['nullable', 'array'],
            'filters.responsible.*' => [Rule::exists(Employee::TABLE, 'id')],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
