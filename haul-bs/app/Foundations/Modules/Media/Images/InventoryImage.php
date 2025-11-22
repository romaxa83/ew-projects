<?php

namespace App\Foundations\Modules\Media\Images;

use Spatie\Image\Manipulations;

class InventoryImage extends ImageAbstract
{
    public function conversions(): array
    {
        return [
            self::SIZE_LARGE => [
                'size' => [
                    'width' => 330,
                    'height' => 330,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 330,
                        'height' => 330,
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
                    'width' => 596,
                    'height' => 596,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 596,
                        'height' => 596,
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
            self::SIZE_MEDIUM => [
                'size' => [
                    'width' => 180,
                    'height' => 180,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 180,
                        'height' => 180,
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
                    'width' => 140,
                    'height' => 140,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 140,
                        'height' => 140,
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
                    'width' => 60,
                    'height' => 60,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 60,
                        'height' => 60,
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
}


