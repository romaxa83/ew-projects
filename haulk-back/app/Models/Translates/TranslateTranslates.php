<?php

namespace App\Models\Translates;

use App\Models\Language;
use App\Traits\ModelTranslates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Translates\TranslateTranslates
 *
 * @property int $id
 * @property string|null $text
 * @property int $row_id
 * @property string $language
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Language $lang
 * @property-read Translate $row
 * @method static Builder|TranslateTranslates newModelQuery()
 * @method static Builder|TranslateTranslates newQuery()
 * @method static Builder|TranslateTranslates query()
 * @method static Builder|TranslateTranslates whereCreatedAt($value)
 * @method static Builder|TranslateTranslates whereId($value)
 * @method static Builder|TranslateTranslates whereLanguage($value)
 * @method static Builder|TranslateTranslates whereRowId($value)
 * @method static Builder|TranslateTranslates whereText($value)
 * @method static Builder|TranslateTranslates whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TranslateTranslates extends Model
{
    use ModelTranslates;

    public CONST TABLE_NAME = 'translates_translates';

    protected $table = self::TABLE_NAME;

    public $fillable = [
        'language',
        'text'
    ];

    public $timestamps = false;
}
