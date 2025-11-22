<?php

namespace App\Http\Requests\Auth\User;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="LoginRequest",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", description="Email", example="test@gmail.com"),
 *     @OA\Property(property="password", type="string", description="Password", example="password"),
 * )
 */
class LoginRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }
    }
}

