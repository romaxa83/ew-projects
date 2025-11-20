<?php

namespace WezomCms\Users\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Users\Rules\EmailCheck;
use WezomCms\Users\Rules\PhoneCheck;

class RegisterRequest extends FormRequest
{
    /**
     *
     *
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:191', new EmailCheck($this->request)],
//            'phone' => ['required', 'string', 'max:191',],
            'phone' => ['required', 'string', 'regex:/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/','max:191', new PhoneCheck($this->request)],
            'token' => ['nullable', 'string'],
            'vehicles' => ['nullable', 'array'],
            'vehicles.*.vinCode' => ['nullable', 'string'],
            'vehicles.*.number' => ['required', 'string'],
            'vehicles.*.year' => ['required'],
            'vehicles.*.isFamilyCar' => ['nullable'],
            'vehicles.*.dealerCenterId' => ['nullable', 'integer', 'exists:dealerships,id'],
            'vehicles.*.brandId' => ['required', 'integer', 'exists:car_brands,id'],
            'vehicles.*.modelId' => ['required', 'integer', 'exists:car_models,id'],
            'vehicles.*.transmissionId' => ['required', 'integer', 'exists:car_transmissions,id'],
            'vehicles.*.engineId' => ['required', 'integer', 'exists:car_engine_types,id'],
            'vehicles.*.engineVolume' => ['required', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
