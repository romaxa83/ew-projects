<?php

namespace App\Models\Warranty\WarrantyInfo;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Warranty\WarrantyInfo\WarrantyInfoTranslationFactory;

/**
 * @property int id
 * @property string description
 * @property string notice
 * @property int row_id
 * @property string language
 *
 * @method static WarrantyInfoTranslationFactory factory(...$parameters)
 */
class WarrantyInfoTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'warranty_info_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'notice',
        'language',
        'row_id',
        'description',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
