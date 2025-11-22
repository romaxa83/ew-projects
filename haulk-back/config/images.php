<?php

return [
    'inspection' => [
        'date_format' => 'g:i A, m/d/Y',

        'text' => [
            /*
             * Full path to font ttf file
             */
            'font' => env('IMAGES_TEXT_FONT', public_path('fonts/GoogleSans-Medium.ttf')),

            /**
             * Image text size in percent
             */
            'size' => env('IMAGES_TEXT_SIZE', 2),

            /*
             * examples: hex '#FF5733',  or rgb 'rgba(255, 255, 255, 0.7)'
             */
            'background_color' => env('IMAGES_TEXT_COLOR', 'rgba(255, 255, 255, 0.7)')
        ],
    ],
];
