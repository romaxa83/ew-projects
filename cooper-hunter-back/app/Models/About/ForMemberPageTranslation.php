<?php

namespace App\Models\About;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\About\ForMemberPageTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static ForMemberPageTranslationFactory factory(...$parameters)
 */
class ForMemberPageTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'for_member_page_translations';

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
