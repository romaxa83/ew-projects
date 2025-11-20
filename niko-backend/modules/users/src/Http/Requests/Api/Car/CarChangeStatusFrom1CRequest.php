<?php

namespace WezomCms\Users\Http\Requests\Api\Car;

use Illuminate\Foundation\Http\FormRequest;

class CarChangeStatusFrom1CRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'AccountID' => ['required'],
            'LicensePlate' => ['required'],
            'VIN' => ['required'],
            'VehicleStatusID' => ['required'],
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
