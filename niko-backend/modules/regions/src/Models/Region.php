<?php

namespace WezomCms\Regions\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 *
 * @property int $id
 * @property bool $published
 * @property int $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \WezomCms\Menu\Models\MenuTranslation $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Menu\Models\MenuTranslation[] $translations
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu filter($input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu orWhereTranslation($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu published()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu publishedWithSlug($slug, $slugField = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\Menu withTranslation()
 * @mixin \Eloquent
 * @mixin RegionTranslation
 */
class Region extends Model
{
	use Translatable;
	use PublishedTrait;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'regions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['published'];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'published' => 'bool'
	];

	/**
	 * Names of the fields being translated in the "Translation" model.
	 *
	 * @var array
	 */
	protected $translatedAttributes = ['name'];

	/**
	 * The relations to eager load on every query.
	 *
	 * @var array
	 */
	protected $with = ['translations'];

	// relations
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}

