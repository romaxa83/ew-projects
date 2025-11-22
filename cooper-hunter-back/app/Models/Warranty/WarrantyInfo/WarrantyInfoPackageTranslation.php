<?php

namespace App\Models\Warranty\WarrantyInfo;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Warranty\WarrantyInfo\WarrantyInfoPackageTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static WarrantyInfoPackageTranslationFactory factory(...$parameters)
 */
class WarrantyInfoPackageTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'warranty_info_package_translations';

    public $timestamps = false;

    protected $table = self::TABLE;
}
