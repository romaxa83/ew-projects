<?php

namespace App\Models\Report\Feature;

use App\Helpers\ConvertLangToLocale;
use App\ModelFilters\Feature\FeatureFilter;
use App\Models\JD\EquipmentGroup;
use App\Traits\ActiveTrait;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $type
 * @property string $type_field
 * @property string $type_feature
 * @property string $name // todo кандидат на удаление
 * @property string $unit // todo кандидат на удаление
 * @property boolean $has_value
 * @property boolean $active
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read FeatureTranslation $current
 * @property-read Collection|FeatureTranslation[] $translations
 * @property-read Collection|FeatureValue[] $values
 */

class Feature extends Model
{
    use ActiveTrait;
    use HasFactory;
    use Filterable;

    const TYPE_GROUND = 1;
    const TYPE_MACHINE = 2;

    const TYPE_FIELD_STRING = 'string';
    const TYPE_FIELD_INT = 'integer';
    const TYPE_FIELD_BOOL = 'boolean';
    const TYPE_FIELD_SELECT = 'select';

    const TYPE_FIELD_INT_FOR_FRONT = 0;
    const TYPE_FIELD_STRING_FOR_FRONT = 1;
    const TYPE_FIELD_BOOL_FOR_FRONT = 2;
    const TYPE_FIELD_SELECT_FOR_FRONT = 3;

    const TYPE_FEATURE_CROP = 'crop'; // тип культуры

    // 0 - int, 1 - string, 2 - boo,

    const TABLE = 'reports_features';
    protected $table = self::TABLE;

    protected $casts = [
        'has_value' => 'boolean',
        'active' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(FeatureFilter::class);
    }

    public function isCrop(): bool
    {
        return $this->type_feature === self::TYPE_FEATURE_CROP;
    }

    public function forGround(): bool
    {
        return $this->type == self::TYPE_GROUND;
    }

    public function forMachine(): bool
    {
        return $this->type == self::TYPE_MACHINE;
    }

    public function isIntegerField(): bool
    {
        return $this->type_field == self::TYPE_FIELD_INT;
    }

    public function egs(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentGroup::class,
            FeatureEGPivot::tableName(),
            'feature_id',
            'eg_id'
        );
    }

    public function subEgs(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentGroup::class,
            FeatureSubEGPivot::tableName(),
            'feature_id',
            'eg_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(FeatureValue::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FeatureTranslation::class, 'feature_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(FeatureTranslation::class,'feature_id', 'id')
            ->where('lang', ConvertLangToLocale::convert(\App::getLocale()));
    }

    public function getTypeFieldForFrontAttribute()
    {
        switch ($this->type_field) {
            case self::TYPE_FIELD_STRING:
                return self::TYPE_FIELD_STRING_FOR_FRONT;
            case self::TYPE_FIELD_INT:
                return self::TYPE_FIELD_INT_FOR_FRONT;
            case self::TYPE_FIELD_BOOL:
                return self::TYPE_FIELD_BOOL_FOR_FRONT;
            case self::TYPE_FIELD_SELECT:
                return self::TYPE_FIELD_SELECT_FOR_FRONT;
        }
    }

    public static function convertTypeFieldToDB($typeField)
    {
        switch ($typeField) {
            case self::TYPE_FIELD_STRING_FOR_FRONT:
                return self::TYPE_FIELD_STRING;
            case self::TYPE_FIELD_INT_FOR_FRONT:
                return self::TYPE_FIELD_INT;
            case self::TYPE_FIELD_BOOL_FOR_FRONT:
                return self::TYPE_FIELD_BOOL;
            case self::TYPE_FIELD_SELECT_FOR_FRONT:
                return self::TYPE_FIELD_SELECT;
        }
    }
}
