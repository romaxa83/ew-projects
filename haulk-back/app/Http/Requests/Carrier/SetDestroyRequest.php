<?php

namespace App\Http\Requests\Carrier;

use App\Rules\Carrier\CheckDestroyToken;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class SetDestroyRequest extends FormRequest
{

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('company-settings delete');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'in:confirm,decline'
            ],
            'token' => [
                'required',
                'string',
                new CheckDestroyToken($this->user()->getCompany(), $this->type)
            ]
        ];
    }
}
