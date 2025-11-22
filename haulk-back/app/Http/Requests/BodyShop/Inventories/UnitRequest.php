<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'name' => ['required', 'string'],
            'accept_decimals' => ['required', 'boolean'],
        ];
    }
}
