<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::SITE => [
        'email_verification' => [
            'greeting' => 'Здравствуйте, :Name!',
            'subject' => 'Подтверждение электронной почты',
            'line_1' => 'Спасибо за регистрацию! Для подтверждения электронной почты нажмите на кнопку ниже.',
            'action' => 'Подтвердить Email',
        ],

        'forgot_password' => [
            'greeting' => 'Здравствуйте, :Name!',
            'subject' => 'Сброс пароля',
            'line_1' => 'Вы запросили сброс пароля',
            'line_2' => 'Чтобы сбросить пароль, нажмите на кнопку ниже:',
            'line_3' => 'Если вы не запрашивали сброс пароля, проигнорируйте это письмо.',
            'action' => 'Скинуть пароль',
        ],
    ],
];
