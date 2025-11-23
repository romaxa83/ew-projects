<?php

return [
    'quality' => 90,
    'watermark' => [
        'path' => 'images/core/watermark.png',
        'cover' => true, // Determines whether the watermark should cover the entire image
        'position' => 'top-left',
        'size' => 100, // Size in percent
        'offset' => [
            'x' => 0,
            'y' => 0,
        ],
        'opacity' => 80, // Opacity in percent. 0 - full transparency, 100 - opaque
    ],
    'storage' => env('IMAGE_STORAGE', 'public'),
    'original_storage' => env('ORIGINAL_IMAGE_STORAGE', 'public'),
    'placeholders' => [
        'directory' => 'images/core',
        'default' => 'no-image.png',
    ],
];
