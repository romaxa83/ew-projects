<?php

namespace WezomCms\Users\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\PhonePatterns;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for register user",
 *     required={"phone", "name", "surname", "email", "actionToken"}
 * )
 */
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email',
                Rule::unique('users', 'email')
            ],
            'phone' => ['required', 'string',
                new PhonePatterns,'max:191',
                Rule::unique('users', 'phone')
            ],
            'actionToken' => ['required', 'string'],
            'deviceId' => ['nullable', 'string'],
            'fcmToken' => ['nullable', 'string'],
            'lang' => ['nullable', 'string'],
            'ref_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'actionToken' => __('cms-users::site.validation.actionToken.field'),
            'name' => __('cms-users::site.validation.name.field'),
            'surname' => __('cms-users::site.validation.surname.field'),
            'email' => __('cms-users::site.validation.email.field'),
            'phone' => __('cms-users::site.validation.phone.field'),
        ];
    }

    public function messages(): array
    {
        return [
            'actionToken.required' => __('cms-users::site.validation.actionToken.required'),
            'name.required' => __('cms-users::site.validation.name.required'),
            'surname.required' => __('cms-users::site.validation.surname.required'),
            'email.required' => __('cms-users::site.validation.email.required'),
            'email.unique' => __('cms-users::site.validation.email.unique'),
            'phone.required' => __('cms-users::site.validation.phone.required'),
            'phone.unique' => __('cms-users::site.validation.phone.unique'),
        ];
    }

    /**
     * @OA\Property(property="name", title="Name", description="Имя пользователя", example="Иван")
     * @OA\Property(property="surname", title="Surname", description="Фамилия пользователя", example="Иванов")
     * @OA\Property(property="email", title="Email", description="Email", example="ivan@gmail.com")
     * @OA\Property(property="phone", title="Phone", description="Телефон", example="+380954545667")
     * @OA\Property(property="actionToken", title="Actio token", description="Actio token - выданные через sms петлю", example="7b11027f-1913-411a-b5ec-8878ef3a7c30")
     * @OA\Property(property="deviceId", title="Device ID", description="ID устройства", example="546B8CF9-1815-4C5C-8432-526FFAFA77E4")
     * @OA\Property(property="fcmToken", title="FCM token", description="Токен для firebase", example="fKGQ-phPK06Uijf-KpqrWg:APA91bEynyefBbg8CCZB0_4wQepQ3n8ztZBwI4jyCDfmz2Ej-98OhAhRbvrS6MgU7DEn7j3qKtp5D4fnGDV2Bph7tyuR8LGOnA1OPIY9W6FxHrUVyYzv2i__G2nphcRXamJWKJ6LPEZa")
     * @OA\Property(property="lang", title="Language", description="Локаль для пользователя", example="ru")
     * @OA\Property(property="ref_id", title="Referral", description="Идентификатор 'пригласившего' пользователя", example=25)
     */
}
