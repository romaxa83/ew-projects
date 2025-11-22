<?php

namespace App\Http\Requests\Auth;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="Reset Password Request",
 *     required={"email"},
 *     @OA\Property(property="password", type="string", description="New password"),
 *     @OA\Property(property="password_confirmation", type="string", description="Password confirmation"),
 *     @OA\Property(property="token", type="string", description="Token from fogrot password reset",
 *         example="def5020088df2ca413818352cf5bc074eb6a83db523da9393f6a551f4d056e81220aa2c1a9b6d06"
 *     ),
 * )
 */

class ResetPasswordRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->passwordRule(),
            [
                'token' => ['nullable', 'string']
            ]
        );
    }
}

