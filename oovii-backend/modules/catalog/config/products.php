<?php

return [
    'images' => [
        'directory' => 'products/images',
        'default' => 'small',
        'sizes' => [
            'small' => [
                'width' => 92,
                'height' => 92,
                'mode' => 'resize',
            ],
            'medium' => [
                'width' => 256,
                'height' => 256,
                'mode' => 'resize',
            ],
            'big' => [
                'width' => 1024,
                'height' => 1024,
                'mode' => 'resize',
            ],
        ],
    ],
    'filter' => [
        'parameters' => [
            // 'brand' => [
            //     'mode' => 'multiple',
            // ],
            // 'model' => [
            //     'mode' => 'multiple',
            // ],
            'cost-from' => [
                'mode' => 'number_range',
            ],
            'cost-to' => [
                'mode' => 'number_range',
            ],
            'specifications' => [
                'mode' => 'multiple',
            ],
        ],
    ],
    'sort' => [
        'url_key' => 'sort',
        'variants' => [
            'default' => [
                'name' => 'cms-catalog::site.products.sort.Default',
                'field' => [
                    'sort' => 'ASC',
                    'id' => 'DESC',
                ],
            ],
            'cost-asc' => [
                'name' => 'cms-catalog::site.products.sort.Cost asc',
                'field' => 'cost',
                'direction' => 'ASC',
            ],
            'cost-desc' => [
                'name' => 'cms-catalog::site.products.sort.Cost desc',
                'field' => 'cost',
                'direction' => 'DESC',
            ],
            'created-at' => [
                'name' => 'cms-catalog::site.products.sort.Created at',
                'field' => 'id',
                'direction' => 'DESC',
            ],
        ],
    ],
    'flags' => [
        'colors' => [
            'popular' => '#010A0B',
            'best_price' => '#E6F7F9',
            'sale' => '#E64134',
            'novelty' => '#E6F7F9',
        ],
    ]
];
