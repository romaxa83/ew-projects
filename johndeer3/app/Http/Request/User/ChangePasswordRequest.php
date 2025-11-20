<?php

namespace App\Http\Request\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Change Password Request",
 *     @OA\Property(property="password", type="string", description="Пароль", example="password12"),
 *     @OA\Property(property="password_confirmation", type="string", description="Потверждения пароля", example="password12"),
 *     required={"password", "password_confirmation"}
 * )
 */

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string|min:5|confirmed'
        ];
    }
}
