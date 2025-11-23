<?php

namespace WezomCms\Users\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\PhonePatterns;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for refrash token",
 *     required={"refreshToken"}
 * )
 */
class RefreshTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'refreshToken' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'refreshToken.required' => __('cms-users::admin.validation.refreshToken.required'),
        ];
    }

    /**
     * @OA\Property(property="refreshToken", title="Refresh token", description="Refresh token - выданные авторизации", example="fKGQ-phPK06Uijf-KpqrWg:APA91bEynyefBbg8CCZB0_4wQepQ3n8ztZBwI4jyCDfmz2Ej-98OhAhRbvrS6MgU7DEn7j3qKtp5D4fnGDV2Bph7tyuR8LGOnA1OPIY9W6FxHrUVyYzv2i__G2nphcRXamJWKJ6LPEZa")
     */
}
