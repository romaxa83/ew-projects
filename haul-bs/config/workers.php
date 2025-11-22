<?php

return [
    'remove' => [
        'orders' => [
            'parts' => [
                // через сколько будут удалены черновики заказа
                'draft_time' =>  env('WORKERS_REMOVE_ORDER_PARTS_DRAFT', 60 * 24)  // min
            ]
        ],
    ]
];
