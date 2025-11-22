<?php

return [
    'firebase' => [
        'action_order_complete' => [
            'title' => 'Запрос выполнен',
            'body' => ':service , :car, :number'
        ],
        'action_service_payment' => [
            'title' => 'Оплата за сервис',
            'body' => ':service , :car, :number'
        ],
        'action_car_moderate' => [
            'title' => 'Администратор',
            'body' => 'Автомобиль прошел модерацию и добавлен в список ваших машин :car :number'
        ],
        'action_email_verify' => [
            'title' => 'Администратор',
            'body' => 'На ваш адрес электронной почты было отправлено письмо с подтверждением. Пожалуйста, проверьте почту.'
        ],
        'action_order_accept' => [
            'title' => 'Ваша заявка принята',
            'body' => 'Ваша заявка была успешно модерирована нашим Консультантом. Ждем вас в нашем Дилерском Центре!'
        ],
        'action_order_remind' => [
            'title' => 'Напоминание о записи!',
            'body' => 'У вас есть заявка на :date'
        ],
        'action_recommend_service' => [
            'title' => 'Рекомендации по обслуживанию',
            'body' => 'У вас новая рекомендация :service :car :number'
        ],
        'action_reconciliation_work' => [
            'title' => 'Координация работ',
            'body' => 'Посмотреть работу на согласование :service :car :number'
        ],
        'action_promotions' => [
            'title' => 'Акции',
            'body' => ':name'
        ],
        'action_discounts' => [
            'title' => 'Скидка',
            'body' => 'Вам предоставили скидку на :name в количестве :discount %'
        ],
        'action_edit_phone_success' => [
            'title' => 'Телефон изменен',
            'body' => 'Телефон изменен :newPhone'
        ],
        'action_edit_phone_error' => [
            'title' => 'Телефон не изменен',
            'body' => 'Телефон не изменен :newPhone'
        ],
        'action_can_add_car_to_garage' => [
            'title' => 'Авто в заказе',
            'body' => 'Можно добавить машину в гараж'
        ],
        'action_agreement' => [
            'title' => 'Доп. согласования',
            'body' => 'Доп. согласования принято'
        ],
    ],
];
