<?php

namespace App\Http\Requests\GPS;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class CarrierSpeedLimitRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'speed_limit' => ['required', 'numeric', 'max:99.9'],
        ];
    }
}
