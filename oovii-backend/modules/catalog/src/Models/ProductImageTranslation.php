<?php

namespace WezomCms\Catalog\Models;

/**
 * WezomCms\Catalog\Models\ProductImageTranslation
 *
 * @property int $id
 * @property int $product_image_id
 * @property string $locale
 * @property string|null $alt
 * @property string|null $title
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation whereAlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation whereProductImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImageTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ProductImageTranslation extends \Illuminate\Database\Eloquent\Model
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
    protected $fillable = ['alt', 'title'];
}
