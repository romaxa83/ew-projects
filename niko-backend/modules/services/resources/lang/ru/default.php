<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'Services' => 'Услуги',
        'Service' => 'Услуга',
        'Services list' => 'Список услуг',
        'Name' => 'Название',
        'Published at' => 'Опубликовано',
        'Image' => 'Изображение',
        'Text' => 'Текст',
        'Site services limit at page' => 'Количество отображаемых услуг на странице',
        'Group' => 'Группа',
        'Groups' => 'Группы',
        'Service groups' => 'Группы услуг',
        'Groups slug' => 'Алиас групп',
        'Service group' => 'Группа',
    ],
    TranslationSide::SITE => [
        'Services' => 'Услуги',
        'exception' => [
            'data type of service does not exist' => 'данные тип сервиса не существует',
            'not request for free time' => 'для данного тип сервиса (:type) не возможности запросить свободное время'
        ],
    ],
];
