<?php

namespace WezomCms\Pages\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Pages\Models\Page
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PageTranslation $translation
 * @property-read Collection|PageTranslation[] $translations
 * @method static Builder|Page filter($input = array(), $filter = null)
 * @method static Builder|Page listsTranslations($translationField)
 * @method static Builder|Page newModelQuery()
 * @method static Builder|Page newQuery()
 * @method static Builder|Page notTranslatedIn($locale = null)
 * @method static Builder|Page orWhereTranslation($translationField, $value, $locale = null)
 * @method static Builder|Page orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Page orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static Builder|Page paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Page published()
 * @method static Builder|Page publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|Page query()
 * @method static Builder|Page simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Page translated()
 * @method static Builder|Page translatedIn($locale = null)
 * @method static Builder|Page whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Page whereCreatedAt($value)
 * @method static Builder|Page whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Page whereId($value)
 * @method static Builder|Page whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Page whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static Builder|Page whereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Page whereUpdatedAt($value)
 * @method static Builder|Page withTranslation()
 * @mixin Eloquent
 * @mixin PageTranslation
 */
class Page extends Model
{
    use Translatable;
    use Filterable;
    use GetForSelectTrait;
    use HasFactory;
    use PublishedTrait;

    public const TABLE = 'pages';

    public const TYPE_PRIVATE_POLICY = 'private-policy';
    public const TYPE_RULES = 'rules';
    public const TYPE_AGREEMENT = 'agreement';

    protected $table = self::TABLE;

    protected $fillable = [
        'type'
    ];

    protected $translatedAttributes = [
        'published',
        'slug',
        'name',
        'text',
        'title',
        'h1',
        'keywords',
        'description'
    ];

    protected $with = ['translation'];

    public static function list(): array
    {
        return [
            self::TYPE_PRIVATE_POLICY => __('cms-pages::admin.type.private-policy'),
            self::TYPE_AGREEMENT => __('cms-pages::admin.type.agreement'),
            self::TYPE_RULES => __('cms-pages::admin.type.rules'),
        ];
    }
}
