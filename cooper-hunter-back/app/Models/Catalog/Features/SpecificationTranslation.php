<?php

namespace App\Models\Catalog\Features;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Features\SpecificationTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static SpecificationTranslationFactory factory(...$parameters)
 */
class SpecificationTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'specification_translations';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'row_id',
        'language',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
