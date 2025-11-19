<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Lang;

/**
 * @see HasTranslations::scopeAddName()
 *
 * @method Builder|static addName(string $field = 'title', ?string $as = null)
 *
 * @see HasTranslations::scopeJoinTranslation()
 *
 * @method Builder|static joinTranslation(string $lang = 'en')
 *
 * @property string $translationModel
 * @property string $translationForeignKey
 */
trait HasTranslations
{
    public static function tableName(): string
    {
        $currentClass = static::class;

        return (new $currentClass())->getTable();
    }

    public function translations(): HasMany
    {
        return $this->hasMany($this->translationModelName(), 'row_id');
    }

    public function translationModelName(): string
    {
        return $this->translationModel ?: static::class . 'Translation';
    }

    public function translation(): HasOne
    {
        return $this->hasOne($this->translationModelName(), 'row_id')
            ->where('language', Lang::getLocale());
    }

    public function dataForCurrentLanguage($default = null)
    {
        $translations = $this->translations;
        foreach ($translations as $translation) {
            /** @phpstan-ignore-next-line */
            if ($translation->language === config('app.locale')) {
                return $translation;
            }
        }

        return $default;
    }

    public function dataFor($lang, $default = null)
    {
        $translations = $this->translations;
        foreach ($translations as $translation) {
            /** @phpstan-ignore-next-line */
            if ($translation->language === $lang) {
                return $translation;
            }
        }

        return $default;
    }

    public function scopeJoinTranslation(Builder $b, ?string $lang = null): void
    {
        if (is_null($lang)) {
            $lang = app()->getLocale();
        }

        $translationTable = $this->getTranslationTableName();

        $b->join(
            $translationTable,
            $translationTable . '.row_id',
            '=',
            $this->getTable() . '.id'
        )->where($translationTable . '.language', $lang);
    }

    public function getTranslationTableName(): string
    {
        $translateModelName = $this->translationModelName();

        return app($translateModelName)->getTable();
    }

    public function scopeAddName(Builder|self $b, string $field = 'name', ?string $as = null): void
    {
        $asField = $as ? $field . " as $as" : $field;

        $b->joinTranslation(app()->getLocale())
            ->addSelect($this->getTranslationTableName() . '.' . $asField);
    }
}
