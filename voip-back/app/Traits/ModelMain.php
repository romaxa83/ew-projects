<?php

namespace App\Traits;

use App\Models\BaseTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Lang;

/**
 * @property Collection|BaseTranslation[] $translates
 * @property BaseTranslation $translate
 */
trait ModelMain
{
    public static function getTranslateTableName()
    {
        $translateModelName = static::translateModelName();
        return app($translateModelName)->getTable();
    }

    public static function translateModelName(): string
    {
        return static::class . 'Translates';
    }

    public static function tableName()
    {
        $currentClass = static::class;
        $relatedModel = new $currentClass();
        return $relatedModel->getTable();
    }

    public function translates(): HasMany
    {
        return $this->hasMany(self::translateModelName(), 'row_id', 'id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(self::translateModelName(), 'row_id', 'id')
            ->where('language', Lang::getLocale());
    }

    public function dataForCurrentLanguage($default = null)
    {
        $translates = $this->translates;
        foreach ($translates as $translate) {
            if ($translate->language === config('app.locale')) {
                return $translate;
            }
        }
        return $default;
    }

    public function dataFor($lang, $default = null)
    {
        $translates = $this->translates;
        foreach ($translates as $translate) {
            if ($translate->language === $lang) {
                return $translate;
            }
        }
        return $default;
    }
}
