<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::ADMIN => [
        'admin_not_exists' => 'Адміністратора не існує',
        'this_admin_has_been_deactivated' => 'Цього адміністратора було деактивовано',
        'custom' => [
            'reset_password' => [
                'time' => 'Час очікування для скидання пароля минув',
                'user' => 'Користувач не знайдений',
                'code' => 'Недійсне посилання для скидання пароля',
            ],
        ],
    ],
];
