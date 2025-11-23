<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'company' => [
            'Company' => 'Компания',
            'Companies' => 'Компании',
            'Name' => 'Название компании',
            'Address' => 'Адрес',
        ],
        'provider' => [
            'Provider' => 'Поставщик',
            'Providers' => 'Поставщики',
            'Name' => 'ФИО контактного лица',
            'status' => [
                'Draft' => 'создан',
                'Moderated' => 'прошел модерацию',
            ],
            'choice' => 'Выбрать провайдера',
            'Products count' => 'Товаров',
            'Provider products' => 'Товары поставщика',
        ],
        'Status' => 'Статус',
        'Active' => 'Активно',
        'Email verified' => 'Вериф. почта',
        'Phone verified' => 'Вериф. телефон',
        'exception' => [
            'Invalid provider status' => 'Invalid provider status [:status]'
        ],
        'notification' => [
            'register new provider' => [
                'title' => 'Зарегистрировался новый провайдер',
                'description' => 'Зарегистрировался новый провайдер, нужно потверждение',
            ]
        ],
        'product show' => 'Просмотр товаров поставщика'
    ],
    TranslationSide::SITE => [
    ],
];
