<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Enums\Fueling\FuelingStatusEnum;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexFuelingHistoryRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                'string',
                FuelingHistoryStatusEnum::ruleIn(),
            ],
            'not_completed' => [
                'nullable',
                'boolean',
            ],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
        ];
    }
}
