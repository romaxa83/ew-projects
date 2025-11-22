<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class SameVinRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vin' => ['required', 'string', 'max:191', 'alpha_num'],
            'id' => ['nullable', 'integer'],
        ];
    }
}
