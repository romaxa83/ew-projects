<?php

namespace App\Http\Requests\Vehicles;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property string vin
 */
class DecodeVinRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'vin' => ['required', 'string']
        ];
    }
}
