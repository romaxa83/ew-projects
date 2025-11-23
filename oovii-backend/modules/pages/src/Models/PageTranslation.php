<?php

namespace WezomCms\Pages\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * \WezomCms\Pages\Models\PageTranslation
 *
 * @property int $id
 * @property int $page_id
 * @property bool $published
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $text
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereH1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Pages\Models\PageTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class PageTranslation extends Model
{
    use MultiLanguageSluggableTrait;

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
    protected $fillable = ['published', 'slug', 'name', 'text', 'title', 'h1', 'keywords', 'description'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['published' => 'bool'];
}
