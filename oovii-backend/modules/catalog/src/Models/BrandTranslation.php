<?php

namespace WezomCms\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Catalog\Models\BrandTranslation
 *
 * @property int $id
 * @property int $brand_id
 * @property string $name
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\BrandTranslation whereName($value)
 * @mixin \Eloquent
 */
class BrandTranslation extends Model
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
