<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'template' => [
            'one' => "Шаблон пуш-уведомления",
            'many' => "Шаблоны пуш-уведомлений",
            'form' => [
                'title' => 'Заголовок уведомления',
                'text' => 'Текст уведомления',
                'vars' => 'Переменные',
                'active' => 'Активировать',
            ]
        ],
        'type' => [
            'title' => 'Тип шаблона',
            'test' => 'тестовый',
            'registry' => 'регистрация',
            'collection_soon_finish' => 'скорое завершение коллекции',
            'collection_start' => 'старт коллекции',
            'orders_status_changed' => 'изменение статуса заказа',
        ],
        'exception' => [
            'not_found_template' => 'not found template - [:template]',
            'not_found_strategy' => 'not found strategy - [:strategy]'
        ]
    ],
    TranslationSide::SITE => [

    ],
];

