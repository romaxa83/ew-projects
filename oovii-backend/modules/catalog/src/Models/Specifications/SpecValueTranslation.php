<?php

namespace WezomCms\Catalog\Models\Specifications;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Catalog\Models\Specifications\SpecValueTranslation
 *
 * @property int $id
 * @property int $spec_value_id
 * @property string $locale
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Specifications\SpecValueTranslation whereSpecValueId($value)
 * @mixin \Eloquent
 */
class SpecValueTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
