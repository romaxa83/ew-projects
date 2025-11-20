<?php

namespace App\Models\Report\Feature;

use App\Helpers\ConvertLangToLocale;
use App\Models\JD\EquipmentGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $feature_id
 * @property-read FeatureValueTranslates $current
 * @property-read Collection|FeatureValueTranslates[] $translates
 */

class FeatureValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    const TABLE = 'feature_values';
    protected $table = self::TABLE;

    public function translates(): HasMany
    {
        return $this->hasMany(
            FeatureValueTranslates::class,
            'value_id',
            'id'
        );
    }

    public function current(): HasOne
    {
        return $this->hasOne(FeatureValueTranslates::class, 'value_id', 'id')
            ->where('lang',ConvertLangToLocale::convert(\App::getLocale()));
    }
}
