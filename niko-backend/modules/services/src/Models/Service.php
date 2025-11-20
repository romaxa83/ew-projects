<?php

namespace WezomCms\Services\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Services\Models\Service
 *
 * @property int $id
 * @property bool $published
 * @property int|null $service_group_id
 * @property int $sort
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \WezomCms\Services\Models\ServiceGroup|null $group
 * @property-read \WezomCms\Services\Models\ServiceTranslation $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Services\Models\ServiceTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service filter($input = array(), $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service orWhereTranslation($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service published()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service publishedWithSlug($slug, $slugField = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereServiceGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\Service withTranslation()
 * @mixin \Eloquent
 * @mixin ServiceTranslation
 */
class Service extends Model
{
    use Translatable;
    use ImageAttachable;
    use GetForSelectTrait;
    use Filterable;
    use PublishedTrait;

    public const TYPE_NONE   = 0;
    public const TYPE_REPAIR = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published', 'service_group_id'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name', 'text'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['published' => 'bool'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.services.services.images'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(ServiceGroup::class, 'service_group_id');
    }

    public function isTypeRepair()
    {
        return $this->type == self::TYPE_REPAIR;
    }
}
