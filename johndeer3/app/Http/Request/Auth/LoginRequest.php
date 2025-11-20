<?php

namespace App\Http\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Login user Request",
 *     @OA\Property(property="login", type="string", description="Логин пользователя", example="cubic"),
 *     @OA\Property(property="password", type="string", description="Пароль пользователя", example="password12"),
 *     @OA\Property(property="fcm_token", type="string", description="Fcm токен, для пуш уведомлений",
 *         example="cDOlCymiRZ-wepSyOrNE-t:APA91bEzOqegwlGP_AuURUtHiHXRdB40uy_SUpZK61pWyRbL-iYiLCJ1Az9gjgXbRPeIf70HLsZqcno2cJNtgi24GSJLZI8cJ1LuezmOModGPBol-qroHJkrKisVWR4UDo_2nJIvK21j"
 *     ),
 *     required={"login", "password", "fcm_token"}
 * )
 */

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'fcm_token' => ['nullable', 'string'],
        ];
    }
}
