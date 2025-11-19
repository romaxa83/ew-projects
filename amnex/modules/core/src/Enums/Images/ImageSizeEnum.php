<?php

namespace Wezom\Core\Enums\Images;

enum ImageSizeEnum: string
{
    case SMALL = 'small';
    case SMALL_WEBP = 'small_webp';
    case SMALL2X_WEBP = 'small2x_webp';
    case MEDIUM = 'medium';
    case MEDIUM_WEBP = 'medium_webp';
    case MEDIUM2X_WEBP = 'medium2x_webp';
    case BIG = 'big';
    case BIG_WEBP = 'big_webp';
    case BIG2X_WEBP = 'big2x_webp';

    public static function webP(): array
    {
        return [
            self::SMALL_WEBP,
            self::SMALL2X_WEBP,
            self::MEDIUM_WEBP,
            self::MEDIUM2X_WEBP,
            self::BIG_WEBP,
            self::BIG2X_WEBP,
        ];
    }

    public function isWebP(): bool
    {
        return in_array($this, self::webP());
    }

    public function getWebpSize(): ImageSizeEnum
    {
        return self::from($this->value . '_webp');
    }

    public function getWebp2xSize(): ImageSizeEnum
    {
        return self::from($this->value . '2x_webp');
    }

    public function is2xSize(): bool
    {
        return in_array(
            $this,
            [
                self::SMALL2X_WEBP,
                self::MEDIUM2X_WEBP,
                self::BIG2X_WEBP,
            ]
        );
    }
}
