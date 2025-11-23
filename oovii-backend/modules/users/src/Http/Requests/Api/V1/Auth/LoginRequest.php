<?php

namespace WezomCms\Users\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Rules\PhonePatterns;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for login user",
 *     required={"phone", "actionToken"}
 * )
 */
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', new PhonePatterns],
            'actionToken' => ['required', 'string'],
            'deviceId' => ['nullable', 'string'],
            'fcmToken' => ['nullable', 'string'],
            'lang' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'actionToken.required' => __('cms-users::admin.validation.actionToken.required'),
            'phone.required' => __('cms-users::admin.validation.phone.required'),
        ];
    }

    /**
     * @OA\Property(property="phone", title="Phone", description="Телефон", example="+380954545667")
     * @OA\Property(property="actionToken", title="Actio token", description="Actio token - выданные через sms петлю", example="7b11027f-1913-411a-b5ec-8878ef3a7c30")
     * @OA\Property(property="deviceId", title="Device ID", description="ID устройства", example="546B8CF9-1815-4C5C-8432-526FFAFA77E4")
     * @OA\Property(property="fcmToken", title="FCM token", description="Токен для firebase", example="fKGQ-phPK06Uijf-KpqrWg:APA91bEynyefBbg8CCZB0_4wQepQ3n8ztZBwI4jyCDfmz2Ej-98OhAhRbvrS6MgU7DEn7j3qKtp5D4fnGDV2Bph7tyuR8LGOnA1OPIY9W6FxHrUVyYzv2i__G2nphcRXamJWKJ6LPEZa")
     * @OA\Property(property="lang", title="Language", description="Локаль для пользователя", example="ru")
     */
}
