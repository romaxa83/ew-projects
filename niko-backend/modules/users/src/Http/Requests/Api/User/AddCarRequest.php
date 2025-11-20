<?php

namespace WezomCms\Users\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class AddCarRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'vinCode' => ['nullable', 'string'],
            'number' => ['required', 'string'],
            'year' => ['required'],
            'isFamilyCar' => ['nullable'],
            'dealerCenterId' => ['nullable', 'integer', 'exists:dealerships,id'],
            'brandId' => ['required', 'integer', 'exists:car_brands,id'],
            'modelId' => ['required', 'integer', 'exists:car_models,id'],
            'transmissionId' => ['required', 'integer', 'exists:car_transmissions,id'],
            'engineId' => ['required', 'integer', 'exists:car_engine_types,id'],
            'engineVolume' => ['required', 'string'],
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
