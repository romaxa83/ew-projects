<?php

return [
    'bs' => [
        'do_not_change_finished_status_after' => 1440,                      // time in minutes
        'purge_after' => env('BS_ORDERS_PURGE_AFTER', 30),      // days
        'delete_after' => env('BS_ORDERS_DELETE_AFTER', 730),   // days
    ],
    'parts' => [
        // через сколько дней ужя нельзя будет перевести заказ из статуса 'delivered' в статус 'returned'
        'change_status_delivered_to_returned' => env('PARTS_ORDERS_STATUS_CHANGE_BY_DELIVERED', 30), // days

        'tax' => [
            'illinois' => 10.5 // % от товаров в качестве налога для заказов из штата Иллинойс
        ],
        // через сколько заказ будет просрочен по оплате, в часах
        'over_due' => [
            \App\Enums\Orders\Parts\PaymentTerms::Day_15() => env('BS_ORDERS_PAST_DUE_15', 15 * 24), // 15 days
            \App\Enums\Orders\Parts\PaymentTerms::Day_30() => env('BS_ORDERS_PAST_DUE_30', 30 * 24) // 30 days
        ],
    ]
];

