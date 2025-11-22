<?php

namespace App\Http\Requests\User;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="ChangePasswordRequest",
 *     required={"password", "password_confirmation"},
 *     @OA\Property(property="password", type="string", example="password123"),
 *     @OA\Property(property="password_confirmation", type="string", example="password123"),
 * )
 */

class ChangePasswordRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->passwordRule();
    }
}
