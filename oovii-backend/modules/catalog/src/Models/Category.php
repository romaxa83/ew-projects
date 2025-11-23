<?php

namespace WezomCms\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Catalog\Models\Category
 *
 * @property int $id
 * @property bool $published
 * @property int $sort
 * @property int|null $parent_id
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $icon
 * @property bool $show_on_main
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\CatalogSeoTemplate[] $catalogSeoTemplate
 * @property-read int|null $catalog_seo_template_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Category[] $children
 * @property-read int|null $children_count
 * @property-read \WezomCms\Catalog\Models\Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Product[] $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Category[] $similarCategories
 * @property-read int|null $similar_categories_count
 * @property-read \WezomCms\Catalog\Models\CategoryTranslation $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\CategoryTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category filter($input = array(), $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category orWhereTranslation($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category published()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category publishedWithSlug($slug, $slugField = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category sorting()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereShowOnMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\Category withTranslation()
 * @mixin \Eloquent
 * @mixin CategoryTranslation
 */
class Category extends EloquentModel
{
    use Translatable;
    use ImageAttachable;
    use Filterable;
    use HasFactory;
    use PublishedTrait;
    use OrderBySort;

    protected $fillable = [
        'published',
        'parent_id',
        'sort',
        'show_on_main'
    ];

    protected $translatedAttributes = [
        'name',
        'slug',
        'text',
        'title',
        'h1',
        'keywords',
        'description'
    ];

    protected $casts = [
        'published' => 'bool',
        'show_on_main' => 'bool'
    ];

    protected $with = ['translation'];

    protected static function booted(): void
    {
        static::deleting(function (Category $category) {
            $category->children()->update(['parent_id' => $category->parent_id]);
        });
    }

    public static function getForSiteMap(string $prefix = 'catalog'): array
    {
        return Category::published()
            ->sorting()
            ->get()
            ->map(function (Category $category) use ($prefix) {
                return [
                    'id' => 'catalog-' . $category->id,
                    'parent_id' => $category->parent_id ? $prefix . '-' . $category->parent_id : $prefix,
                    'sort' => $category->sort,
                    'name' => $category->name,
                    'url' => $category->getFrontUrl(),
                ];
            })
            ->toArray();
    }

    public function imageSettings(): array
    {
        return [
            'image' => 'cms.catalog.categories.image',
            'icon' => 'cms.catalog.categories.icon',
        ];
    }

//    public function getFrontUrl(): string
//    {
//        return route('catalog.category', [$this->slug, $this->id]);
//    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function catalogSeoTemplate(): BelongsToMany
    {
        return $this->belongsToMany(CatalogSeoTemplate::class, 'category_catalog_seo_template');
    }

    public function similarCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_by_similar', 'category_id', 'similar_category_id');
    }

    public function getAllRootsCategoriesId(): array
    {
        $result = [];

        $category = $this;

        while ($category->parent_id !== null) {
            $category = Category::published()->find($category->parent_id);
            if (!$category) {
                break;
            }

            $result[] = $category->id;
        }

        return $result;
    }

    public static function getForSelect(callable $callback = null): array
    {
        $query = static::select()
            ->sorting();

        if (null !== $callback) {
            $callback($query);
        }

        $tree = Helpers::groupByParentId($query->get());

        return static::addTreeSpaces($tree);
    }

    private static function addTreeSpaces(array $tree, $id = null, array &$result = [], string $space = ''): array
    {
        foreach ($tree[$id] ?? [] as $group) {
            if (isset($tree[$group->id])) {
                $result[$group->id] = ['disabled' => true, 'name' => $space . $group->name];
                static::addTreeSpaces($tree, $group->id, $result, $space . '&nbsp;&nbsp;&nbsp;&nbsp;');
            } else {
                $result[$group->id] = ['name' => $space . $group->name];
            }
        }

        return $result;
    }
}
