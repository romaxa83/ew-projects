<?php

return [
    'storage' => env('FILE_STORAGE', 'public'),
    'models' => [
        'car' => [
            'class' => \App\Models\User\Car::class,
        ],
        'order' => [
            'class' => \App\Models\Order\Order::class,
        ],
        'page' => [
            'class' => \App\Models\Page\Page::class,
        ],
        'spares' => [
            'class' => \App\Models\Catalogs\Calc\SparesDownloadFile::class,
        ],
    ],
];

