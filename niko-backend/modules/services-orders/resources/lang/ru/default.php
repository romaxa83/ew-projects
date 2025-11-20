<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'Service orders' => 'Заказы услуг',
        'Service' => 'Услуга',
        'Name' => 'Имя',
        'ID' => 'ID',
        'Status' => 'Статус',
        'Phone' => 'Телефон',
        'E-mail' => 'E-mail',
        'City' => 'Город',
        'Message' => 'Сообщение',
        'Read' => 'Прочитано',
        'Unread' => 'Не прочитано',
        'New message from the service order form' => 'Новое сообщение из формы заказа услуги',
        'Requests' => 'Обратная связь',
        'Service orders new' => 'Новые заказы услуг',
        'statuses' => [
            'created' => 'создана',
            'received' => 'получена в 1с',
            'in_work' => 'в обработке в 1с',
            'accepted' => 'принята в 1с',
            'done' => 'выполнена в 1с',
            'rejected' => 'отклонена в 1с',
        ],
        'Service order rate' => 'Оценка заявок',
        'Rating order' => 'Оценка order',
        'Rating services' => 'Оценка service',
        'Rating comment' => 'Комментарий',
        'Date' => 'Дата',
        'Date rate' => 'Дата оценки'
    ],
    TranslationSide::SITE => [
        'exceptions' => [
            'undefined status' => 'Не определеный статус для заявки (:status)',
            'must close status' => 'Заявка должна быть с закрытым статусом',
            'must be sto' => 'Заявка должна быть с СТО',
            'order is have reject status' => 'Заявка , откланена, изменению не подлежит'
        ],
    ],
];
