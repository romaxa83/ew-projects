<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'catalog-seo-templates' => [
            'Category seo template meta-tags keys' => '[category] - category name',
            'Categories meta-tags keys' => '[name] - Name, [id] - ID category',
        ],
        'products' => [
            'Invalid video url' => 'Invalid video url: ":input". Example: https://youtube.com/v=31235423.',
            'Select main product' => 'Select the main item for the current item.',
            'End date of the promotion' => 'End date of the promotion must be greater than the current date',
        ],
    ],
    TranslationSide::SITE => [
        'products' => [
            'pieces' => 'Pieces',
            'Popular products' => 'Top of the week',
        ],
    ],
];
