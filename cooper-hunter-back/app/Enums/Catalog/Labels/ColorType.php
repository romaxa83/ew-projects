<?php

namespace App\Enums\Catalog\Labels;

use Core\Enums\BaseEnum;

/**
 * @method static static YELLOW()
 * @method static static PINK()
 * @method static static GREEN()
 * @method static static RED()
 * @method static static BLUE()
 */
class ColorType extends BaseEnum
{
    public const YELLOW = 'yellow';
    public const PINK   = 'pink';
    public const GREEN  = 'green';
    public const RED    = 'red';
    public const BLUE   = 'blue';

    public function isYellow(): bool
    {
        return $this->is(self::YELLOW());
    }

    public function getTextColor(): string
    {
        return $this->color()[$this->value]['text'];
    }

    public function getBackgroundColor(): string
    {
        return $this->color()[$this->value]['background'];
    }

    private function color():array
    {
        return [
            self::YELLOW => [
                'text' => '98732C',
                'background' => 'FEDA7C',
            ],
            self::PINK => [
                'text' => 'B5598A',
                'background' => 'FFDEEA',
            ],
            self::GREEN => [
                'text' => '7456BB',
                'background' => 'B3F0ED',
            ],
            self::RED => [
                'text' => 'FF0000',
                'background' => 'ECD8DA',
            ],
            self::BLUE => [
                'text' => '006EC3',
                'background' => 'F0F4FD',
            ],
        ];
    }
}


//фон FEDA7C . текст 98732C - yellow
//фон FFDEEA . текст B5598A - pink
//фон B3F0ED . текст 7456BB - green
// фон ECD8DA . текст FF0000 - red
//фон F0F4FD . текст 006EC3 - blue
