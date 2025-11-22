<?php

namespace App\Models\About;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\About\AboutCompanyTranslationFactory;

/**
 * @property int id
 * @property string video_link
 * @property string title
 * @property string description
 * @property string short_description
 * @property int row_id
 * @property string language
 *
 * @method static AboutCompanyTranslationFactory factory(...$parameters)
 */
class AboutCompanyTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'about_company_translations';

    public $timestamps = false;

    protected $fillable = [
        'video_link',
        'title',
        'description',
        'short_description',
        'row_id',
        'language',
        'seo_title',
        'seo_description',
        'seo_h1',
        'additional_title',
        'additional_description',
        'additional_video_link',
    ];
}
