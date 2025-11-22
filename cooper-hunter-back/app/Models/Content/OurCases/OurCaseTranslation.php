<?php

namespace App\Models\Content\OurCases;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Content\OurCases\OurCaseTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static OurCaseTranslationFactory factory(...$parameters)
 */
class OurCaseTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'our_case_translations';

    public $timestamps = false;

    protected $fillable = [
        'language',
        'title',
        'description',
    ];
}
