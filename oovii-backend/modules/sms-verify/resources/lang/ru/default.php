<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'exception' => [
            'action token active' => "Action токен активен",
            'not found action token' => "Не найден Action токен [:token]",
            'expired action token' => "Action токен протух [:token]",
            'sms token active' => "Sms токен активен",
            'not found sms token' => "Не найден Sms токен [:token]",
            'expired sms token' => "Sms токен протух [:token]",
            'not equals sms code' => "код не совпадает",
            'phone or accessToken required' => "Телефон или accessToken обязателен",
        ],
    ],
    TranslationSide::SITE => [
        'messages' => [
            'sms_code' => 'Your code: :code'
        ],
        'exception' => [
            'action token active' => "Action токен активен",
            'not found action token' => "Не найден Action токен [:token]",
            'expired action token' => "Action токен протух [:token]",
            'sms token active' => "Sms токен активен",
            'not found sms token' => "Не найден Sms токен [:token]",
            'expired sms token' => "Sms токен протух [:token]",
            'not equals sms code' => "код не совпадает",
            'phone or accessToken required' => "Телефон или accessToken обязателен",
        ],
    ],
];
