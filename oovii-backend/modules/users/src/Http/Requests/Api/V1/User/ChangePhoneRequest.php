<?php

namespace WezomCms\Users\Http\Requests\Api\V1\User;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\PhonePatterns;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Change a user's phone"
 * )
 */
class ChangePhoneRequest extends FormRequest
{
    public function rules(): array
    {
        $user = Auth::user();

        return [
            'phone' => ['required', 'string',
                new PhonePatterns,'max:191',
                Rule::unique('users', 'phone')
                    ->ignore($user->id)
            ],
            'actionToken' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    /**
     * @OA\Property(property="phone", title="Phone", description="Телефон", example="+380954545667")
     * @OA\Property(property="actionToken", title="Actio token", description="Actio token - выданные через sms петлю", example="7b11027f-1913-411a-b5ec-8878ef3a7c30")
     */
}


