<?php

namespace App\Foundations\Modules\Media\Images;

use Spatie\Image\Manipulations;

class UserProfileImage extends ImageAbstract
{
    public function getField() :string
    {
        return 'photo';
    }

    public function conversions(): array
    {
        return [
            self::SIZE_EXTRA_SMALL => [
                'size' => [
                    'width' => 70,
                    'height' => 70,
                ],
                'manipulations' => [
                    'sharpen' => [10],
//                    'fit' => [
//                        Manipulations::FIT_CROP,
//                        'width' => 70,
//                        'height' => 70,
//                    ],
                ],
                'formats' => [
                    Manipulations::FORMAT_WEBP,
                    Manipulations::FORMAT_JPG,
                ],
//                self::X2 => [
//                    Manipulations::FORMAT_WEBP
//                ]
            ],
            self::SIZE_SMALL => [
                'size' => [
                    'width' => 200,
                    'height' => 200,
                ],
                'manipulations' => [
                    'sharpen' => [10],
//                    'fit' => [
//                        Manipulations::FIT_CROP,
//                        'width' => 200,
//                        'height' => 200,
//                    ],
                ],
                'formats' => [
                    Manipulations::FORMAT_WEBP,
                    Manipulations::FORMAT_JPG,
                ],
//                self::X2 => [
//                    Manipulations::FORMAT_WEBP
//                ]
            ],
            self::SIZE_MEDIUM => [
                'size' => [
                    'width' => 500,
                    'height' => 500,
                ],
                'manipulations' => [
                    'sharpen' => [10],
//                    'fit' => [
//                        Manipulations::FIT_CROP,
//                        'width' => 500,
//                        'height' => 500,
//                    ],
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

