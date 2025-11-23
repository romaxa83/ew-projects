<?php

namespace WezomCms\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Catalog\Models\CatalogSeoTemplateTranslation
 *
 * @property int $id
 * @property int $catalog_seo_template_id
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $description
 * @property string|null $keywords
 * @property string $locale
 * @property string|null $text
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereCatalogSeoTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereH1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class CatalogSeoTemplateTranslation extends Model
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
    protected $fillable = ['title', 'h1', 'keywords', 'description', 'text'];
}
