<?php

namespace WezomCms\Users\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Rules\PhonePatterns;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for check user by phobe",
 *     required={"phone"}
 * )
 */
class CheckByPhoneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', new PhonePatterns],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'phone.required' => __('cms-users::admin.validation.phone.required'),
        ];
    }

    /**
     * @OA\Property(property="phone", title="Phone", description="Телефон", example="+380954545667")
     */
}
