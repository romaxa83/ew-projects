<?php

namespace App\Rules\Auth;

use App\Models\Auth\MemberPhoneVerification;
use Illuminate\Contracts\Validation\Rule;

class SmsConfirmationTokenRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $code = MemberPhoneVerification::query()
            ->where('sms_token', $value['token'])
            ->first();

        if (!$code) {
            return false;
        }

        if ($code->code !== $value['code']) {
            return false;
        }

        return $code->sms_token_expires_at > now();
    }

    public function message(): string
    {
        return __('validation.custom.sms_token_invalid');
    }
}
