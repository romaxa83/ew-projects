<?php

namespace App\Http\Requests\Auth;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Users\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="Forgot Password Request",
 *     required={"email"},
 *     @OA\Property(property="email", type="string", description="User email", example="test@test.com"),
 * )
 */

class ForgotPasswordRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:filter', Rule::exists(User::TABLE, 'email')],
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
