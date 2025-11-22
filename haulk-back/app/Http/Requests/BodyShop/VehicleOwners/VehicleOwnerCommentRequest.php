<?php

namespace App\Http\Requests\BodyShop\VehicleOwners;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class VehicleOwnerCommentRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }
}
