<?php

namespace App\Models\Files;

class PaymentImage extends ImageAbstract
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
