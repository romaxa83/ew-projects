<?php

namespace App\Models\Content\OurCases;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Content\OurCases\OurCaseCategoryTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string slug
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static OurCaseCategoryTranslationFactory factory(...$parameters)
 */
class OurCaseCategoryTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'our_case_category_translations';

    public $timestamps = false;

    protected $fillable = [
        'language',
        'title',
        'description',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
