<?php

namespace App\Models\Faq;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Faq\FaqTranslationFactory;

/**
 * @property int id
 * @property string question
 * @property string answer
 * @property int row_id
 * @property string language
 *
 * @method static FaqTranslationFactory factory(...$parameters)
 */
class FaqTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'faq_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'question',
        'answer',
        'row_id',
        'language',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
