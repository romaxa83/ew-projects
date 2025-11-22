<?php

namespace App\Foundations\Modules\Media\Images;

use Spatie\Image\Manipulations;

class SeoImage extends ImageAbstract
{
    public function conversions(): array
    {
        return [
            self::SIZE_EXTRA_LARGE => [
                'size' => [
                    'width' => 540,
                    'height' => 298,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 540,
                        'height' => 298,
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
                    'width' => 288,
                    'height' => 298,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                    'fit' => [
                        Manipulations::FIT_CROP,
                        'width' => 288,
                        'height' => 298,
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
