<?php

namespace App\Traits\Auth;

use GraphQL\Type\Definition\Type;

trait SmsConfirmable
{
    protected function smsAccessTokenArg(): array
    {
        return [
            'sms_access_token' => [
                'type' => Type::string(),
                'description' => 'Если передан, и валидный, то телефон будет подтвержден'
            ]
        ];
    }

    protected function smsAccessTokenRule(bool $required = false): array
    {
        return [
            'sms_access_token' => [$required ? 'required' : 'nullable', 'string']
        ];
    }
}
