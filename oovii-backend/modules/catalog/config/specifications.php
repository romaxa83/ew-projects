<?php

return [
    'has_an_image' => false,
    'images' => [
        'directory' => 'specifications',
        'default' => 'medium',
        'sizes' => [
            'medium' => [
                'width' => 400,
                'height' => 400,
                'mode' => 'resize',
            ],
        ],
    ],
];
