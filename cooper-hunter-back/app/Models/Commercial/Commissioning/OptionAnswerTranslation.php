<?php

namespace App\Models\Commercial\Commissioning;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Commercial\Commissioning\OptionAnswerTranslationFactory;

/**
 * @property integer id
 * @property string text
 * @property int row_id
 * @property string language
 *
 * @method static OptionAnswerTranslationFactory factory(...$parameters)
 */

class OptionAnswerTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'commissioning_option_answer_translations';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'text',
        'row_id',
        'language',
    ];
}
