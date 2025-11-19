<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::ADMIN => [
        'admin_not_exists' => 'Администратор не существует',
        'this_admin_has_been_deactivated' => 'Этот администратор деактивирован',
        'custom' => [
            'reset_password' => [
                'time' => 'Тайм-аут сброса пароля истек',
                'user' => 'Пользователь не найден',
                'code' => 'Неверная ссылка для сброса пароля',
            ],
        ],
    ],
];
