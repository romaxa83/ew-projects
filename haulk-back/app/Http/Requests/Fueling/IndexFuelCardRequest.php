<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexFuelCardRequest extends FormRequest
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
            'q' => [
                'nullable',
                'string',
                'min:2',
            ],
            'driver_id' => ['nullable', 'integer', Rule::exists(User::TABLE_NAME, 'id')],
            'provider' => [
                'nullable',
                'string',
                FuelCardProviderEnum::ruleIn(),
            ],
            'status' => [
                'nullable',
                'string',
                FuelCardStatusEnum::ruleIn(),
            ],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
        ];
    }
}
