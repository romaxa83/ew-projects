<?php

namespace WezomCms\SmsVerify\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Rules\PhonePatterns;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for sms verify",
 *     required={"phone"}
 * )
 */
class SmsVerifyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required_without:accessToken', 'string', new PhonePatterns],
            'accessToken' => ['required_without:phone', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'phone.required_without' => __('cms-sms-verify::admin.exception.phone or accessToken required'),
            'accessToken.required_without' => __('cms-sms-verify::admin.exception.phone or accessToken required'),
        ];
    }

    /**
     * @OA\Property(property="phone", title="Phone", description="Телефон", example="+380954545667")
     */
}
