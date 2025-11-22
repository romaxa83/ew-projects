<?php

namespace App\Models\Files\BodyShop;

use App\Models\Files\ImageAbstract;

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
            ],
        ];
    }
}
