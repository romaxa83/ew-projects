<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::SITE => [
        'email_verification' => [
            'greeting' => 'Вітаємо, :Name!',
            'subject' => 'Підтвердження електронної пошти',
            'line_1' => 'Дякуємо за реєстрацію! Для підтвердження електронної пошти натисніть на кнопку нижче.',
            'action' => 'Підтвердити Email',
        ],

        'forgot_password' => [
            'greeting' => 'Вітаємо, :Name!',
            'subject' => 'Скидання пароля',
            'line_1' => 'Ви запросили скидання паролю',
            'line_2' => 'Щоб скинути пароль - натисніть на кнопку нижче:',
            'line_3' => 'Якщо ви не запрошували скидання пароля - проігноруйте цей лист.',
            'action' => 'Скинути пароль',
        ],
    ],
];
