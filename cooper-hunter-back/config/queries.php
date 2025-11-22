<?php

return [
    'default' => [
        'pagination' => [
            'per_page' => env('DEFAULT_PAGINATION_PER_PAGE', 15),
        ],
    ],

    'localization' => [
        'translations' => [
            'cache' => env('QUERIES_TRANSLATES_CACHE', 3600),
        ],

        'translates_filterable' => [
            'limit' => env('QUERIES_TRANSLATES_FILTERABLE_LIMIT', 50),
        ],

        'languages' => [
            'cache' => env('QUERIES_LANGUAGES_CACHE', 3600),
        ],

        'locales' => [
            'cache' => env('QUERIES_LOCALES_CACHE', 3600),
        ],
    ],
];
