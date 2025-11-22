<?php

namespace App\Models\Files;

class NewsImage extends ImageAbstract
{
    public function conversions(): array
    {
        return [
            self::SIZE_SMALL => [
                'size' => [
                    'width' => 400,
                    'height' => 300,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                ],
            ],
        ];
    }
}
