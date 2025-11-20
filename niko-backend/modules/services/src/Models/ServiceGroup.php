<?php

namespace WezomCms\Services\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Services\Types\ServiceType;

/**
 * \WezomCms\Services\Models\ServiceGroup
 *
 * @property int $id
 * @property bool $published
 * @property int $sort
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Services\Models\Service[] $services
 * @property-read int|null $services_count
 * @property-read \WezomCms\Services\Models\ServiceGroupTranslation $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Services\Models\ServiceGroupTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup filter($input = array(), $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup orWhereTranslation($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup published()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup publishedWithSlug($slug, $slugField = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Services\Models\ServiceGroup withTranslation()
 * @mixin \Eloquent
 * @mixin ServiceGroupTranslation
 */
class ServiceGroup extends Model
{
    use Translatable;
    use Filterable;
    use GetForSelectTrait;
    use PublishedTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name'];

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

    public function isInsurance()
    {
        return ServiceType::isInsurance($this->type);
    }

    public function isSto()
    {
        return ServiceType::isSto($this->type);
    }

    public function isTestDrive()
    {
        return ServiceType::isTestDrive($this->type);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
