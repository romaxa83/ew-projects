<?php

return [
    'filter' => [
        'min_query_length' => env('ADMIN_FILTER_MIN_QUERY_LENGTH', 3),
    ],

    'paginate' => [
        'per_page' => env('ADMINS_PAGINATE_PER_PAGE', 50),
        'max_per_page' => env('ADMINS_MAX_PER_PAGE', 100),
    ],
];
