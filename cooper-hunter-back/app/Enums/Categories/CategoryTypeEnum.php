<?php


namespace App\Enums\Categories;


use Core\Enums\BaseEnum;

/**
 * Class CategoryTypeEnum
 * @package App\Enums\Categories
 *
 * @method static static accessories()
 * @method static static COMMERCIAL()
 */
class CategoryTypeEnum extends BaseEnum
{
    public const accessories = 'accessories';
    public const COMMERCIAL = 'commercial';

    public static function getType(string $title): ?self
    {
        $title = mb_convert_case($title, MB_CASE_LOWER);

        return match ($title) {
            'accessories' => self::accessories(),
            'commercial' => self::COMMERCIAL(),
            default => null
        };
    }
}
