<?php

namespace WezomCms\Services\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * \WezomCms\Services\Models\ServiceTranslation
 *
 * @property int $id
 * @property int $service_id
 * @property string $name
 * @property string|null $text
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereH1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ServiceTranslation extends Model
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
    protected $fillable = ['name', 'text'];
}
