<?php

return [
    // верификация
    'verify_email' => [
        'enabled' =>  true,
        'email_token_expired' => 'PT5M',     // 5 мин
        // через сколько дней, будут удален,не использованные токены
        'old_days' => 2
    ],
    'car' => [
        // создание фейкового доверенного лица (для разработки)
        'enabled_fake_confidant' => false
    ]
];
