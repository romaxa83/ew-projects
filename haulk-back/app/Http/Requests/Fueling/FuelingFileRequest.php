<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class FuelingFileRequest extends FormRequest
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
            'file' => [
                'required',
                'mimes:csv',
                "max:" . byte_to_kb(10 * 1024 * 1024),
            ],
            'provider' => ['required', 'string', FuelCardProviderEnum::ruleIn()],
        ];
    }
}
