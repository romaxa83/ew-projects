<?php

use App\Models\History\History;
use App\Models\Users\User;

return [

    'enabled' => true,

    'histories_table' => History::TABLE_NAME,

    'events_whitelist' => [
        'created', 'updating', 'deleting', 'restored',
    ],


    'attributes_blacklist' => [
         User::class => [
             'password',
         ],
    ],

    'user_blacklist' => [
            // 3,2 users ids
    ],

    'logs' => [
        'keep' => env('DB_LOGS_KEEP', 60), //days

        'paginate' => [
            'per-page' => env('LOG_DB_PER_PAGE', 50),
        ],
    ],
];
