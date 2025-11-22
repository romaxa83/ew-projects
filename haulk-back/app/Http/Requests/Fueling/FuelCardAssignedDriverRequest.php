<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Fueling\FuelCardAssignedTypeEnum;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class FuelCardAssignedDriverRequest extends FormRequest
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
            'driver_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', FuelCardAssignedTypeEnum::ruleIn()],
        ];
    }
}
