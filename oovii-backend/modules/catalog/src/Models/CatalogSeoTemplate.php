<?php

namespace WezomCms\Catalog\Models;

use DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WezomCms\Catalog\Filter\Contracts\TemplateParametersInterface;
use WezomCms\Catalog\Filter\Handlers\BrandHandler;
use WezomCms\Catalog\Filter\Handlers\CostHandler;
use WezomCms\Catalog\Filter\Handlers\SpecificationHandler;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Catalog\Models\CatalogSeoTemplate
 *
 * @property int $id
 * @property bool $published
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\CatalogSeoTemplateParameter[] $catalogSeoTemplateParameters
 * @property-read int|null $catalog_seo_template_parameters_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read \WezomCms\Catalog\Models\CatalogSeoTemplateTranslation $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\CatalogSeoTemplateTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate filter($input = array(), $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate orWhereTranslation($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate published()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate publishedWithSlug($slug, $slugField = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\CatalogSeoTemplate withTranslation()
 * @mixin \Eloquent
 * @mixin CatalogSeoTemplateTranslation
 */
class CatalogSeoTemplate extends EloquentModel
{
    use Translatable;
    use Filterable;
    use PublishedTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published', 'name'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['title', 'h1', 'keywords', 'description', 'text'];

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
    protected $with = ['translation'];

    /**
     * @return HasMany
     */
    public function catalogSeoTemplateParameters()
    {
        return $this->hasMany(CatalogSeoTemplateParameter::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_catalog_seo_template');
    }

    /**
     * @return array
     */
    public static function availableParameters()
    {
        $availableParameters = [];
        $handlers = [
            BrandHandler::class,
            CostHandler::class,
            SpecificationHandler::class,
        ];

        foreach ($handlers as $handler) {
            /** @var TemplateParametersInterface $handler */
            $availableParameters = $availableParameters + $handler::availableParameters();
        }

        return $availableParameters;
    }

    /**
     * Use default template from settings
     * @param  Category  $category
     * @param  string|null  $title
     * @param  string|null  $h1
     * @param  string|null  $keywords
     * @param  string|null  $description
     * @return array
     */
    public static function applyDefaultTemplate(
        Category $category,
        ?string $title,
        ?string $h1,
        ?string $keywords,
        ?string $description
    ) {
        $settings = settings('catalog-seo-templates.default-template', []);

        if (!$title) {
            $title = array_get($settings, 'title');
        }
        if (!$h1) {
            $h1 = array_get($settings, 'h1');
        }
        if (!$keywords) {
            $keywords = array_get($settings, 'keywords');
        }
        if (!$description) {
            $description = array_get($settings, 'description');
        }

        // Setup variables in meta
        $replace = [
            '[name]' => $category->name,
            '[id]' => $category->id,
        ];
        $from = array_keys($replace);
        $to = array_values($replace);

        $title = str_replace($from, $to, $title);
        $h1 = str_replace($from, $to, $h1);
        $keywords = str_replace($from, $to, $keywords);
        $description = str_replace($from, $to, $description);

        return [$title, $h1, $keywords, $description];
    }

    /**
     * @param  Category  $category
     * @param  array  $selectedParameters
     * @return \Illuminate\Database\Eloquent\Builder|EloquentModel|object
     */
    public static function searchTemplateByParameters(Category $category, $selectedParameters = [])
    {
        $count = count($selectedParameters);
        if (!$count) {
            return null;
        }

        $queryBuilder = $category->catalogSeoTemplate()
            ->published()
            ->addSelect('catalog_seo_templates.id')
            ->join(
                'catalog_seo_template_parameters',
                'catalog_seo_template_parameters.catalog_seo_template_id',
                '=',
                'catalog_seo_templates.id'
            );

        $queryBuilder->where(function ($query) use ($selectedParameters) {
            $query->whereIn('parameter', $selectedParameters);
        });

        $queryBuilder->where(DB::raw('(select count(catalog_seo_template_id)
           from catalog_seo_template_parameters
           where catalog_seo_template_parameters.catalog_seo_template_id = catalog_seo_templates.id)'), $count);

        $countQuery = DB::raw('COUNT(DISTINCT catalog_seo_template_parameters.parameter)');
        $queryBuilder->having($countQuery, '=', $count);
        $queryBuilder->groupBy('catalog_seo_templates.id', 'category_catalog_seo_template.category_id');

        return $queryBuilder->first();
    }
}
