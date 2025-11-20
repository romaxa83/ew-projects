<?php

namespace WezomCms\Services\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * \WezomCms\Services\Models\ServiceGroupTranslation
 *
 * @property int $id
 * @property int $service_group_id
 * @property string $name
 * @property string $slug
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereH1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereServiceGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroupTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ServiceGroupTranslation extends Model
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
    protected $fillable = ['name', 'slug', 'title', 'h1', 'keywords', 'description'];
}
