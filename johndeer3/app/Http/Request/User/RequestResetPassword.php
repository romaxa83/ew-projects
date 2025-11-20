<?php

namespace App\Http\Request\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Reset Password Request",
 *     @OA\Property(property="email", description="Email", example="cubic@rubic.com"),
 *     required={"email"}
 * )
 */
class RequestResetPassword extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:191', 'exists:users,email'],
        ];
    }
}
