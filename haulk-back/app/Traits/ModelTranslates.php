<?php namespace App\Traits;

use App\Models\Language;
use Illuminate\Support\Str;

/**
 * Trait ModelTranslates
 *
 * Методы для работы с таблицей с переводами
 *
 * @package App\Traits
 *
 * @property string language
 * @property int row_id
 *
 * @property-read object|mixed|null $row
 * @property-read Language $lang
 */
trait ModelTranslates
{
    public function rules()
    {
        return [];
    }

    public function relatedModelName()
    {
        $currentClass = static::class;
        $mainClass = Str::replaceLast('Translates', '', $currentClass);
        return $mainClass;
    }

    public function row()
    {
        $modelName = $this->relatedModelName();
        return $this->belongsTo($modelName, 'row_id', 'id');
    }

    public function lang()
    {
        return $this->belongsTo(Language::class, 'slug', 'language');
    }

}
