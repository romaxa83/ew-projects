<?php

namespace App\Models\Files;

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
                ],
            ],
            self::SIZE_SMALL => [
                'size' => [
                    'width' => 200,
                    'height' => 200,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                ],
            ],
            self::SIZE_MEDIUM => [
                'size' => [
                    'width' => 500,
                    'height' => 500,
                ],
                'manipulations' => [
                    'sharpen' => [10],
                ],
            ],
        ];
    }
}
