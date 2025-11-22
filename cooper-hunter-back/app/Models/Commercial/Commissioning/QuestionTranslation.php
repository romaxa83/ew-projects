<?php

namespace App\Models\Commercial\Commissioning;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Commercial\Commissioning\QuestionTranslationFactory;

/**
 * @property integer id
 * @property string text
 * @property int row_id
 * @property string language
 *
 * @method static QuestionTranslationFactory factory(...$parameters)
 */

class QuestionTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'commissioning_question_translations';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'text',
        'row_id',
        'language',
    ];
}

