<?php

namespace App\Foundations\Modules\Media\Images;

use Spatie\Image\Manipulations;

class SettingsImage extends ImageAbstract
{
    public function conversions(): array
    {
        return [
            self::SIZE_SMALL => [
                'size' => [
                    'width' => 200,
                    'height' => 200,
                ],
                'manipulations' => [
                    'sharpen' => [10],
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
