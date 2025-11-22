<?php

return [
    'brand' => [
        'color' => [
            'none' => 'None',
            'red' => 'Красный',
            'yellow' => 'Желтый',
            'blue' => 'Синий'
        ]
    ],
    'service' => [
        'insurance_company' => [
            'arsenal' => 'Arsenal of insurance',
            'arks' => 'АРКС'
        ]
    ],
    'communication' => [
        'telegram' => 'Telegram',
        'viber' => 'Viber',
        'phone' => 'Звонок на телефон',
    ],
    'user' => [
        'status' => [
            'draft' => 'создан',
            'active' => 'активен, но не клиент АА',
            'verify' => 'клиент AA',
        ],
        'type' => [
            'personal' => 'Personal',
            'legal' => 'Legal',
        ],
    ],
    'car' => [
        'status' => [
            'draft' => 'создан',
            'moderate' => 'прошел модерацию',
            'verify' => 'верефицирован',
        ],
        'reason' => [
            'sold' => 'продажа авто',
            'other' => 'другое',
        ],
    ],
    'support' => [
        'message' => [
            'status' => [
                'draft' => 'создано',
                'read' => 'прочитано',
                'done' => 'выполнено',
            ],
        ]
    ],
    'order' => [
        'status' => [
            'draft' => 'новая',
            'created' => 'создана',
            'in_process' => 'в работе',
            'done' => 'выполнена',
            'reject' => 'отклонена',
            'close' => 'закрыта',
        ],
        'car' => [
            'state' => [
                'wait' => 'ожидание',
                'current' => 'текущая',
                'done' => 'выполнено',
                'skip' => 'пропущена',
            ]
        ],
        'payment' => [
            'none' => 'none',
            'not' => 'не оплачено',
            'part' => 'частично',
            'full' => 'полностью'
        ],
        'state' => [
            'none' => 'none',
            'created' => 'создана',
            'in process' => 'в процессе',
            'done' => 'готово',
        ]
    ],
    'spares' => [
        'volume' => 'об.',
        'qty' => 'шт.'
    ],
    'work' => [
        'unit' => 'ч.',
    ]
];
