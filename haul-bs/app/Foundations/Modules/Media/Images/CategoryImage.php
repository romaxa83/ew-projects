<?php

namespace App\Foundations\Modules\Media\Images;

use App\Models\Inventories\Category;
use Spatie\Image\Manipulations;

class CategoryImage extends ImageAbstract
{
    public function conversions(): array
    {
        return [
            self::SIZE_SMALL => [
                'size' => [
                    'width' => 96,
                    'height' => 64,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 96,
                        'height' => 64,
                    ],
                ],
                'formats' => [
                    Manipulations::FORMAT_WEBP,
                    Manipulations::FORMAT_JPG,
                ],
                self::X2 => [
                    Manipulations::FORMAT_WEBP
                ]
            ],
            self::SIZE_EXTRA_SMALL => [
                'size' => [
                    'width' => 72,
                    'height' => 48,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 72,
                        'height' => 48,
                    ],
                ],
                'formats' => [
                    Manipulations::FORMAT_WEBP,
                    Manipulations::FORMAT_JPG,
                ],
                self::X2 => [
                    Manipulations::FORMAT_WEBP
                ]
            ],
            self::SIZE_EXTRA_LARGE => [
                'size' => [
                    'width' => 600,
                    'height' => 260,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 600,
                        'height' => 260,
                    ],
                ],
                'formats' => [
                    Manipulations::FORMAT_WEBP,
                    Manipulations::FORMAT_JPG,
                ],
                self::X2 => [
                    Manipulations::FORMAT_WEBP
                ]
            ],
        ];
    }

    public function conversionsSpecial(): array
    {
        return [
            Category::IMAGE_MOBILE_FIELD_NAME => [
                self::SIZE_EXTRA_LARGE => [
                    'size' => [
                        'width' => 531,
                        'height' => 260,
                    ],
                    'manipulations' => [
                        'sharpen' => [10],
                        'fit' => [
                            Manipulations::FIT_CROP,
                            'width' => 531,
                            'height' => 260,
                        ],
                    ],
                    'formats' => [
                        Manipulations::FORMAT_WEBP,
                        Manipulations::FORMAT_JPG,
                    ],
                    self::X2 => [
                        Manipulations::FORMAT_WEBP
                    ]
                ],
                self::SIZE_SMALL => [
                    'size' => [
                        'width' => 96,
                        'height' => 64,
                    ],
                    'manipulations' => [
                        'sharpen' => [10],
                        'fit' => [
                            Manipulations::FIT_CROP,
                            'width' => 96,
                            'height' => 64,
                        ],
                    ],
                    'formats' => [
                        Manipulations::FORMAT_WEBP,
                        Manipulations::FORMAT_JPG,
                    ],
                    self::X2 => [
                        Manipulations::FORMAT_WEBP
                    ]
                ],
                self::SIZE_EXTRA_SMALL => [
                    'size' => [
                        'width' => 72,
                        'height' => 48,
                    ],
                    'manipulations' => [
                        'sharpen' => [10],
                        'fit' => [
                            Manipulations::FIT_CROP,
                            'width' => 72,
                            'height' => 48,
                        ],
                    ],
                    'formats' => [
                        Manipulations::FORMAT_WEBP,
                        Manipulations::FORMAT_JPG,
                    ],
                    self::X2 => [
                        Manipulations::FORMAT_WEBP
                    ]
                ],
            ]
        ];
    }
}

