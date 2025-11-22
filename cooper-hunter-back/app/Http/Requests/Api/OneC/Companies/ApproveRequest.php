<?php

namespace App\Http\Requests\Api\OneC\Companies;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ApproveRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'authorization_code' => ['nullable', 'string', Rule::unique('companies', 'code')]
        ];
    }
}
