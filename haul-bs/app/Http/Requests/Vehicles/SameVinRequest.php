<?php

namespace App\Http\Requests\Vehicles;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property string vin
 * @property string|int|null id
 */
class SameVinRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'vin' => ['required', 'string', 'max:191', 'alpha_num'],
            'id' => ['nullable', 'integer'],
        ];
    }
}
