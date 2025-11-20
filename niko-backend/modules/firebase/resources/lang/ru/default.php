<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [

    ],
    TranslationSide::SITE => [
        'notifications' => [
            'change final date for order' => [
                'title' => 'Сменилось время заявки :service_name',
                'body' => 'Ваша запись на :service_name была перенесена на :date'
            ],
            'order is reject' => [
                'title' => 'Заявка отклонена :service_name',
                'body' => 'Ваша заявка на :service_name была отклонена, более подробно можете обсудить с Администратором Дилерского Центра'
            ],
            'remind order' => [
                'title' => 'У вас назначена заявка :service_name',
                'body' => 'Назначена заявка на :date :service_name'
            ],
            'new promotion' => [
                'title' => 'Новая акция',
                'body' => 'Новая акция'
            ],
            'rate order' => [
                'title' => 'Оценить заявку на :service_name',
                'body' => 'Оценить заявку на :service_name'
            ],
            'verify car' => [
                'title' => 'Верификация авто',
                'body' => 'Администратор свяжется с Вами для верификации Авто'
            ],
            'order is accepted' => [
                'title' => 'Заявка на :service_name',
                'body' => 'Ваша заявка на :service_name была получена. Ожидаем Вас :date в :time'
            ],
        ],
    ],
];
