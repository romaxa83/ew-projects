<?php

namespace App\Http\Request\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Set FcmToken Request",
 *     @OA\Property(property="fcm_token", type="string", example="cDOlCymiRZ-wepSyOrNE-t:APA91bEzOqegwlGP_AuURUtHiHXRdB40uy_SUpZK61pWyRbL-iYiLCJ1Az9gjgXbRPeIf70HLsZqcno2cJNtgi24GSJLZI8cJ1LuezmOModGPBol-qroHJkrKisVWR4UDo_2nJIvK21j"),
 *     required={"fcm_token", "password_confirmation"}
 * )
 */

class SetFcmTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string'],
        ];
    }
}

