<?php

namespace App\Http\Requests\Vehicles;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class VehicleCommentRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }
}
